<?php

function contactTableHasColumn(mysqli $conn, $columnName)
{
    $safeColumn = $conn->real_escape_string((string) $columnName);
    $result = $conn->query("SHOW COLUMNS FROM contacts LIKE '{$safeColumn}'");

    return $result instanceof mysqli_result && $result->num_rows > 0;
}

function contactTableHasIndex(mysqli $conn, $indexName)
{
    $safeIndex = $conn->real_escape_string((string) $indexName);
    $result = $conn->query("SHOW INDEX FROM contacts WHERE Key_name = '{$safeIndex}'");

    return $result instanceof mysqli_result && $result->num_rows > 0;
}

function getContactStorageConnection()
{
    if (!class_exists('mysqli') || !defined('DB_HOST') || !defined('DB_USER') || !defined('DB_PASS') || !defined('DB_NAME')) {
        return null;
    }

    $conn = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    if ($conn->connect_errno) {
        return null;
    }

    $conn->set_charset('utf8mb4');

    return $conn;
}

function contactBindParams(mysqli_stmt $stmt, $types, array $params)
{
    if ($types === '') {
        return true;
    }

    $bindValues = [$types];
    foreach ($params as $key => $value) {
        $bindValues[] = &$params[$key];
    }

    return call_user_func_array([$stmt, 'bind_param'], $bindValues);
}

function ensureContactsTableExists(mysqli $conn)
{
    $sql = "CREATE TABLE IF NOT EXISTS contacts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        ticket_code VARCHAR(32) DEFAULT NULL,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL,
        phone VARCHAR(20) DEFAULT NULL,
        subject VARCHAR(255) DEFAULT NULL,
        message TEXT NOT NULL,
        status ENUM('new', 'processing', 'resolved') DEFAULT 'new',
        priority VARCHAR(20) NOT NULL DEFAULT 'normal',
        admin_note TEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        resolved_at TIMESTAMP NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    if ($conn->query($sql) !== true) {
        return false;
    }

    $columnStatements = [
        'ticket_code' => "ALTER TABLE contacts ADD COLUMN ticket_code VARCHAR(32) DEFAULT NULL AFTER id",
        'priority' => "ALTER TABLE contacts ADD COLUMN priority VARCHAR(20) NOT NULL DEFAULT 'normal' AFTER status",
        'admin_note' => "ALTER TABLE contacts ADD COLUMN admin_note TEXT NULL AFTER priority",
        'updated_at' => "ALTER TABLE contacts ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at"
    ];

    foreach ($columnStatements as $column => $statement) {
        if (!contactTableHasColumn($conn, $column) && $conn->query($statement) !== true) {
            return false;
        }
    }

    if (!contactTableHasIndex($conn, 'uniq_contacts_ticket_code') &&
        $conn->query("ALTER TABLE contacts ADD UNIQUE KEY uniq_contacts_ticket_code (ticket_code)") !== true) {
        return false;
    }

    if (!contactTableHasIndex($conn, 'idx_contacts_status') &&
        $conn->query("ALTER TABLE contacts ADD KEY idx_contacts_status (status)") !== true) {
        return false;
    }

    if (!contactTableHasIndex($conn, 'idx_contacts_priority') &&
        $conn->query("ALTER TABLE contacts ADD KEY idx_contacts_priority (priority)") !== true) {
        return false;
    }

    return backfillContactTicketCodes($conn);
}

function buildContactTicketCode($contactId)
{
    return 'LH-' . str_pad((string) max(1, (int) $contactId), 6, '0', STR_PAD_LEFT);
}

function backfillContactTicketCodes(mysqli $conn)
{
    $result = $conn->query("SELECT id FROM contacts WHERE ticket_code IS NULL OR ticket_code = '' ORDER BY id ASC");
    if (!$result) {
        return false;
    }

    $stmt = $conn->prepare('UPDATE contacts SET ticket_code = ? WHERE id = ?');
    if (!$stmt) {
        return false;
    }

    while ($row = $result->fetch_assoc()) {
        $contactId = (int) $row['id'];
        $ticketCode = buildContactTicketCode($contactId);
        $stmt->bind_param('si', $ticketCode, $contactId);
        if (!$stmt->execute()) {
            $stmt->close();
            return false;
        }
    }

    $stmt->close();
    return true;
}

function getContactStatusOptions()
{
    return [
        'new' => ['label' => 'Mới', 'class' => 'is-new'],
        'processing' => ['label' => 'Đang xử lý', 'class' => 'is-processing'],
        'resolved' => ['label' => 'Đã giải quyết', 'class' => 'is-resolved']
    ];
}

function getContactPriorityOptions()
{
    return [
        'low' => ['label' => 'Thấp', 'class' => 'is-low'],
        'normal' => ['label' => 'Bình thường', 'class' => 'is-normal'],
        'high' => ['label' => 'Cao', 'class' => 'is-high'],
        'urgent' => ['label' => 'Khẩn', 'class' => 'is-urgent']
    ];
}

function normalizeContactStatus($status)
{
    $status = trim((string) $status);
    $options = getContactStatusOptions();

    return array_key_exists($status, $options) ? $status : 'new';
}

function normalizeContactPriority($priority)
{
    $priority = trim((string) $priority);
    $options = getContactPriorityOptions();

    return array_key_exists($priority, $options) ? $priority : 'normal';
}

function contactStatusLabel($status)
{
    $status = normalizeContactStatus($status);
    $options = getContactStatusOptions();

    return $options[$status]['label'];
}

function contactStatusClass($status)
{
    $status = normalizeContactStatus($status);
    $options = getContactStatusOptions();

    return $options[$status]['class'];
}

function contactPriorityLabel($priority)
{
    $priority = normalizeContactPriority($priority);
    $options = getContactPriorityOptions();

    return $options[$priority]['label'];
}

function contactPriorityClass($priority)
{
    $priority = normalizeContactPriority($priority);
    $options = getContactPriorityOptions();

    return $options[$priority]['class'];
}
function getContactStats()
{
    $conn = getContactStorageConnection();
    if (!$conn || !ensureContactsTableExists($conn)) {
        return ['total' => 0, 'new' => 0, 'processing' => 0, 'resolved' => 0];
    }

    $stats = ['total' => 0, 'new' => 0, 'processing' => 0, 'resolved' => 0];
    
    $result = $conn->query("SELECT status, COUNT(*) as count FROM contacts GROUP BY status");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $stats[$row['status']] = (int) $row['count'];
            $stats['total'] += (int) $row['count'];
        }
    }
    
    $conn->close();
    return $stats;
}