<?php

function getProcedureStorageConnection()
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

function procedureBindParams(mysqli_stmt $stmt, $types, array $params)
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

function procedureTableHasColumn(mysqli $conn, $columnName)
{
    $safeColumn = $conn->real_escape_string((string) $columnName);
    $result = $conn->query("SHOW COLUMNS FROM administrative_procedures LIKE '{$safeColumn}'");

    return $result instanceof mysqli_result && $result->num_rows > 0;
}

function procedureTableHasIndex(mysqli $conn, $indexName)
{
    $safeIndex = $conn->real_escape_string((string) $indexName);
    $result = $conn->query("SHOW INDEX FROM administrative_procedures WHERE Key_name = '{$safeIndex}'");

    return $result instanceof mysqli_result && $result->num_rows > 0;
}

function ensureProceduresTableExists(mysqli $conn)
{
    $createSql = "CREATE TABLE IF NOT EXISTS administrative_procedures (
        id INT AUTO_INCREMENT PRIMARY KEY,
        code VARCHAR(50) NOT NULL,
        title VARCHAR(255) NOT NULL,
        category VARCHAR(50) NOT NULL DEFAULT 'khac',
        summary TEXT NOT NULL,
        required_documents TEXT NULL,
        process_steps LONGTEXT NULL,
        processing_time VARCHAR(100) NULL,
        fee VARCHAR(100) NULL,
        form_url VARCHAR(255) NULL,
        form_label VARCHAR(255) NULL,
        secondary_form_url VARCHAR(255) NULL,
        secondary_form_label VARCHAR(255) NULL,
        form_note TEXT NULL,
        official_source_url VARCHAR(255) NULL,
        contact_point VARCHAR(255) NULL,
        is_featured TINYINT(1) NOT NULL DEFAULT 0,
        display_order INT NOT NULL DEFAULT 0,
        status VARCHAR(20) NOT NULL DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    if ($conn->query($createSql) !== true) {
        return false;
    }

    $columnStatements = [
        'code' => "ALTER TABLE administrative_procedures ADD COLUMN code VARCHAR(50) NOT NULL AFTER id",
        'title' => "ALTER TABLE administrative_procedures ADD COLUMN title VARCHAR(255) NOT NULL AFTER code",
        'category' => "ALTER TABLE administrative_procedures ADD COLUMN category VARCHAR(50) NOT NULL DEFAULT 'khac' AFTER title",
        'summary' => "ALTER TABLE administrative_procedures ADD COLUMN summary TEXT NOT NULL AFTER category",
        'required_documents' => "ALTER TABLE administrative_procedures ADD COLUMN required_documents TEXT NULL AFTER summary",
        'process_steps' => "ALTER TABLE administrative_procedures ADD COLUMN process_steps LONGTEXT NULL AFTER required_documents",
        'processing_time' => "ALTER TABLE administrative_procedures ADD COLUMN processing_time VARCHAR(100) NULL AFTER process_steps",
        'fee' => "ALTER TABLE administrative_procedures ADD COLUMN fee VARCHAR(100) NULL AFTER processing_time",
        'form_url' => "ALTER TABLE administrative_procedures ADD COLUMN form_url VARCHAR(255) NULL AFTER fee",
        'form_label' => "ALTER TABLE administrative_procedures ADD COLUMN form_label VARCHAR(255) NULL AFTER form_url",
        'secondary_form_url' => "ALTER TABLE administrative_procedures ADD COLUMN secondary_form_url VARCHAR(255) NULL AFTER form_label",
        'secondary_form_label' => "ALTER TABLE administrative_procedures ADD COLUMN secondary_form_label VARCHAR(255) NULL AFTER secondary_form_url",
        'form_note' => "ALTER TABLE administrative_procedures ADD COLUMN form_note TEXT NULL AFTER secondary_form_label",
        'official_source_url' => "ALTER TABLE administrative_procedures ADD COLUMN official_source_url VARCHAR(255) NULL AFTER form_note",
        'contact_point' => "ALTER TABLE administrative_procedures ADD COLUMN contact_point VARCHAR(255) NULL AFTER official_source_url",
        'is_featured' => "ALTER TABLE administrative_procedures ADD COLUMN is_featured TINYINT(1) NOT NULL DEFAULT 0 AFTER contact_point",
        'display_order' => "ALTER TABLE administrative_procedures ADD COLUMN display_order INT NOT NULL DEFAULT 0 AFTER is_featured",
        'status' => "ALTER TABLE administrative_procedures ADD COLUMN status VARCHAR(20) NOT NULL DEFAULT 'active' AFTER display_order",
        'created_at' => "ALTER TABLE administrative_procedures ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER status",
        'updated_at' => "ALTER TABLE administrative_procedures ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at"
    ];

    foreach ($columnStatements as $column => $statement) {
        if (!procedureTableHasColumn($conn, $column) && $conn->query($statement) !== true) {
            return false;
        }
    }

    if (!procedureTableHasIndex($conn, 'uniq_administrative_procedures_code') &&
        $conn->query("ALTER TABLE administrative_procedures ADD UNIQUE KEY uniq_administrative_procedures_code (code)") !== true) {
        return false;
    }

    if (!procedureTableHasIndex($conn, 'idx_administrative_procedures_category') &&
        $conn->query("ALTER TABLE administrative_procedures ADD KEY idx_administrative_procedures_category (category)") !== true) {
        return false;
    }

    if (!procedureTableHasIndex($conn, 'idx_administrative_procedures_status') &&
        $conn->query("ALTER TABLE administrative_procedures ADD KEY idx_administrative_procedures_status (status)") !== true) {
        return false;
    }

    return seedDefaultProcedures($conn);
}

function getDefaultProcedureSeedItems()
{
    return [
        [
            'code' => 'HC-001',
            'title' => 'Đăng ký khai sinh',
            'category' => 'ho-tich',
            'summary' => 'Thủ tục đăng ký khai sinh tại Ủy ban nhân dân cấp xã theo quy định hộ tịch hiện hành.',
            'required_documents' => "- Tờ khai đăng ký khai sinh theo mẫu 1.TKngkkhaisinh.doc nếu nộp trực tiếp hoặc qua dịch vụ bưu chính.\n- Mẫu hộ tịch điện tử tương tác 1.TTT-ngkkhaisinh.doc nếu nộp trực tuyến.\n- Giấy chứng sinh; nếu không có Giấy chứng sinh thì nộp văn bản của người làm chứng xác nhận việc sinh, nếu không có người làm chứng thì nộp giấy cam đoan về việc sinh.\n- Trường hợp đã nộp bản điện tử Giấy chứng sinh hoặc cơ quan hộ tịch đã khai thác được dữ liệu điện tử có ký số thì không phải nộp bản giấy.\n- Trường hợp trẻ em sinh ra do mang thai hộ thì nộp văn bản xác nhận của cơ sở y tế đã thực hiện kỹ thuật hỗ trợ sinh sản cho việc mang thai hộ.\n- Xuất trình CCCD/hộ chiếu hoặc giấy tờ tùy thân hợp lệ; giấy tờ cư trú khi cơ quan hộ tịch chưa khai thác được dữ liệu; giấy chứng nhận kết hôn nếu cha, mẹ đã đăng ký kết hôn và dữ liệu chưa có trên hệ thống.",
            'process_steps' => "1. Người có yêu cầu nộp hồ sơ trực tiếp, trực tuyến hoặc qua dịch vụ bưu chính tại UBND cấp xã.\n2. Công chức tư pháp - hộ tịch kiểm tra thành phần hồ sơ, đối chiếu giấy tờ, tra cứu dữ liệu cư trú và hộ tịch điện tử.\n3. Nếu hồ sơ đầy đủ, công chức cập nhật Sổ hộ tịch, trình ký và cấp Giấy khai sinh; nếu nhận hồ sơ sau 15 giờ mà không giải quyết ngay thì trả kết quả trong ngày làm việc tiếp theo.",
            'processing_time' => 'Ngay trong ngày tiếp nhận; nếu nhận hồ sơ sau 15 giờ thì trả kết quả trong ngày làm việc tiếp theo.',
            'fee' => 'Miễn lệ phí đối với khai sinh đúng hạn; trường hợp khác thực hiện theo Nghị quyết 01/2025/NQ-HĐND của tỉnh. Miễn lệ phí khi nộp trực tuyến.',
            'form_url' => 'https://csdl.dichvucong.gov.vn/web/jsp/download_file.jsp?ma=3fd5450fb2670b86',
            'form_label' => 'Tờ khai trực tiếp (1.TKngkkhaisinh.doc)',
            'secondary_form_url' => 'https://csdl.dichvucong.gov.vn/web/jsp/download_file.jsp?ma=3fc9c3a1778ff70c',
            'secondary_form_label' => 'Mẫu điện tử tương tác (1.TTT-ngkkhaisinh.doc)',
            'form_note' => 'Biểu mẫu được lấy từ Cổng Dịch vụ công quốc gia. Khi nộp trực tuyến, người dân sử dụng mẫu điện tử tương tác theo đúng hướng dẫn trên cổng.',
            'official_source_url' => 'https://thutuc.dichvucong.gov.vn/p/home/dvc-tthc-thu-tuc-hanh-chinh-chi-tiet.html?ma_thu_tuc=140183',
            'contact_point' => 'Trung tâm Phục vụ hành chính công cấp xã / Công chức Tư pháp - Hộ tịch',
            'is_featured' => 1,
            'display_order' => 1,
            'status' => 'active'
        ],
        [
            'code' => 'HC-002',
            'title' => 'Chứng thực bản sao từ bản chính',
            'category' => 'chung-thuc',
            'summary' => 'Chứng thực bản sao từ bản chính giấy tờ, văn bản do cơ quan có thẩm quyền của Việt Nam hoặc nước ngoài cấp hoặc chứng nhận.',
            'required_documents' => "- Bản chính giấy tờ, văn bản làm cơ sở để chứng thực bản sao.\n- Bản sao cần chứng thực; trường hợp người yêu cầu chỉ xuất trình bản chính thì cơ quan, tổ chức có thể chụp từ bản chính để thực hiện chứng thực nếu có phương tiện.\n- Bản sao từ bản chính phải có đầy đủ các trang đã ghi thông tin của bản chính.",
            'process_steps' => "1. Người yêu cầu xuất trình bản chính và nộp bản sao cần chứng thực tại UBND cấp xã hoặc bộ phận một cửa.\n2. Cán bộ tiếp nhận kiểm tra, đối chiếu bản sao với bản chính và xác định giấy tờ không thuộc trường hợp bị từ chối chứng thực.\n3. Thực hiện chứng thực, thu phí và trả kết quả trong ngày; trường hợp tiếp nhận sau 15 giờ hoặc hồ sơ nhiều trang, số lượng lớn thì trả trong ngày làm việc tiếp theo hoặc tối đa thêm 02 ngày làm việc theo quy định.",
            'processing_time' => 'Trong ngày; nếu tiếp nhận sau 15 giờ thì trong ngày làm việc tiếp theo. Hồ sơ nhiều trang, số lượng lớn có thể kéo dài thêm tối đa 02 ngày làm việc.',
            'fee' => '2.000 đồng/trang đối với 2 trang đầu, từ trang 3 trở lên 1.000 đồng/trang, tối đa 200.000 đồng/bản.',
            'form_url' => '',
            'form_label' => '',
            'secondary_form_url' => '',
            'secondary_form_label' => '',
            'form_note' => 'Thủ tục này không có mẫu đơn, tờ khai riêng. Người dân chỉ cần xuất trình bản chính và bản sao cần chứng thực theo quy định.',
            'official_source_url' => 'https://thutuc.dichvucong.gov.vn/p/home/dvc-tthc-thu-tuc-hanh-chinh-chi-tiet.html?ma_thu_tuc=118314',
            'contact_point' => 'Bộ phận Một cửa UBND cấp xã',
            'is_featured' => 1,
            'display_order' => 2,
            'status' => 'active'
        ],
        [
            'code' => 'HC-003',
            'title' => 'Xác nhận tình trạng hôn nhân',
            'category' => 'ho-tich',
            'summary' => 'Cấp Giấy xác nhận tình trạng hôn nhân để đăng ký kết hôn hoặc sử dụng vào mục đích dân sự khác theo quy định.',
            'required_documents' => "- Tờ khai cấp Giấy xác nhận tình trạng hôn nhân theo mẫu 19.TKcpGiyXNTTHN.doc nếu nộp trực tiếp.\n- Mẫu điện tử tương tác 19.DTTT-CpgiyXNTThnnhn.doc nếu nộp trực tuyến.\n- Trường hợp đã có vợ/chồng nhưng đã ly hôn hoặc vợ/chồng đã chết thì cung cấp thông tin Bản án/Quyết định ly hôn, Giấy chứng tử hoặc Trích lục khai tử để cơ quan hộ tịch tra cứu.\n- Trường hợp xin cấp lại để dùng vào mục đích khác hoặc giấy đã hết hạn thì nộp lại Giấy xác nhận tình trạng hôn nhân đã cấp trước đó.\n- Văn bản ủy quyền hợp lệ nếu ủy quyền thực hiện thủ tục.\n- Xuất trình CCCD/hộ chiếu hoặc giấy tờ tùy thân hợp lệ và giấy tờ cư trú nếu cơ quan hộ tịch chưa khai thác được dữ liệu.",
            'process_steps' => "1. Người yêu cầu nộp hồ sơ trực tiếp, trực tuyến hoặc qua dịch vụ bưu chính tại UBND cấp xã.\n2. Công chức tư pháp - hộ tịch kiểm tra hồ sơ, tra cứu dữ liệu cư trú và hộ tịch, thực hiện xác minh nếu cần.\n3. Trường hợp hồ sơ hợp lệ, UBND cấp xã cấp Giấy xác nhận tình trạng hôn nhân trong 03 ngày làm việc; nếu phải xác minh thì tổng thời hạn giải quyết không quá 23 ngày.",
            'processing_time' => '03 ngày làm việc; trường hợp phải xác minh thì không quá 23 ngày.',
            'fee' => 'Miễn lệ phí khi nộp trực tuyến; các trường hợp khác thực hiện theo Nghị quyết 01/2025/NQ-HĐND của tỉnh và diện miễn, giảm theo quy định.',
            'form_url' => 'https://csdl.dichvucong.gov.vn/web/jsp/download_file.jsp?ma=3fa6f783637ae430',
            'form_label' => 'Tờ khai trực tiếp (19.TKcpGiyXNTTHN.doc)',
            'secondary_form_url' => 'https://csdl.dichvucong.gov.vn/web/jsp/download_file.jsp?ma=3fe1717570074230',
            'secondary_form_label' => 'Mẫu điện tử tương tác (19.DTTT-CpgiyXNTThnnhn.doc)',
            'form_note' => 'Biểu mẫu được lấy từ Cổng Dịch vụ công quốc gia. Khi nộp trực tuyến, người dân dùng mẫu điện tử tương tác theo hướng dẫn trên cổng.',
            'official_source_url' => 'https://thutuc.dichvucong.gov.vn/p/home/dvc-tthc-thu-tuc-hanh-chinh-chi-tiet.html?ma_thu_tuc=140194',
            'contact_point' => 'Trung tâm Phục vụ hành chính công cấp xã / Công chức Tư pháp - Hộ tịch',
            'is_featured' => 0,
            'display_order' => 3,
            'status' => 'active'
        ]
    ];
}

function seedDefaultProcedures(mysqli $conn)
{
    $result = $conn->query("SELECT COUNT(*) AS total FROM administrative_procedures");
    if (!$result) {
        return false;
    }

    $row = $result->fetch_assoc();
    if ((int) ($row['total'] ?? 0) > 0) {
        return true;
    }

    $stmt = $conn->prepare(
        "INSERT INTO administrative_procedures
            (code, title, category, summary, required_documents, process_steps, processing_time, fee, form_url, form_label,
             secondary_form_url, secondary_form_label, form_note, official_source_url, contact_point, is_featured, display_order, status)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );

    if (!$stmt) {
        return false;
    }

    $seedItems = getDefaultProcedureSeedItems();

    foreach ($seedItems as $item) {
        $stmt->bind_param(
            'sssssssssssssssiis',
            $item['code'],
            $item['title'],
            $item['category'],
            $item['summary'],
            $item['required_documents'],
            $item['process_steps'],
            $item['processing_time'],
            $item['fee'],
            $item['form_url'],
            $item['form_label'],
            $item['secondary_form_url'],
            $item['secondary_form_label'],
            $item['form_note'],
            $item['official_source_url'],
            $item['contact_point'],
            $item['is_featured'],
            $item['display_order'],
            $item['status']
        );
        if (!$stmt->execute()) {
            $stmt->close();
            return false;
        }
    }

    $stmt->close();
    return true;
}

function getProcedureCategoryOptions()
{
    return [
        'all' => 'Tất cả lĩnh vực',
        'ho-tich' => 'Hộ tịch',
        'chung-thuc' => 'Chứng thực',
        'dat-dai' => 'Đất đai',
        'lao-dong' => 'Lao động - an sinh',
        'van-hoa' => 'Văn hóa - xã hội',
        'khac' => 'Khác'
    ];
}

function normalizeProcedureCategory($category)
{
    $category = trim((string) $category);
    $options = getProcedureCategoryOptions();

    return array_key_exists($category, $options) ? $category : 'khac';
}

function procedureCategoryLabel($category)
{
    $options = getProcedureCategoryOptions();
    $key = $category === 'all' ? 'all' : normalizeProcedureCategory($category);

    return $options[$key] ?? $options['khac'];
}

function normalizeProcedureStatus($status)
{
    $status = trim((string) $status);

    return in_array($status, ['active', 'inactive'], true) ? $status : 'active';
}

function procedureStatusLabel($status)
{
    return normalizeProcedureStatus($status) === 'active' ? 'Đang áp dụng' : 'Tạm ẩn';
}
