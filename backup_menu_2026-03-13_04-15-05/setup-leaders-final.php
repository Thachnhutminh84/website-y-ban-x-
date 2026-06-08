<?php
// Script thiết lập dữ liệu lãnh đạo - phiên bản cuối cùng
require_once 'config.php';

echo "<h2>🔧 Thiết lập dữ liệu lãnh đạo</h2>";

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Bước 1: Kiểm tra và tạo bảng
    echo "<h3>📋 Bước 1: Kiểm tra bảng</h3>";
    
    $stmt = $pdo->query("SHOW TABLES LIKE 'leaders'");
    if ($stmt->rowCount() == 0) {
        echo "<p style='color: orange;'>⚠️ Bảng 'leaders' chưa tồn tại. Đang tạo bảng...</p>";
        
        // Đọc và thực thi SQL tạo bảng
        $createSQL = file_get_contents('create-leaders-table.sql');
        $pdo->exec($createSQL);
        echo "<p style='color: green;'>✅ Đã tạo bảng 'leaders' và 'leader_work_history'</p>";
    } else {
        echo "<p style='color: green;'>✅ Bảng 'leaders' đã tồn tại</p>";
    }
    
    // Bước 2: Kiểm tra dữ liệu hiện tại
    echo "<h3>📊 Bước 2: Kiểm tra dữ liệu hiện tại</h3>";
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM leaders");
    $count = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p>Số lượng lãnh đạo hiện tại: <strong>{$count['total']}</strong></p>";
    
    if ($count['total'] > 0) {
        echo "<p style='color: orange;'>⚠️ Đã có dữ liệu. Bạn có muốn xóa và thêm lại không?</p>";
        echo "<form method='post'>";
        echo "<button type='submit' name='reset' value='1' style='background: red; color: white; padding: 10px;'>🗑️ Xóa và thêm lại</button>";
        echo "<button type='submit' name='keep' value='1' style='background: green; color: white; padding: 10px; margin-left: 10px;'>✅ Giữ nguyên</button>";
        echo "</form>";
        
        if (isset($_POST['keep'])) {
            echo "<p style='color: green;'>✅ Giữ nguyên dữ liệu hiện tại</p>";
            echo "<p><a href='lanh-dao.php'>👀 Xem trang lãnh đạo</a></p>";
            exit;
        }
        
        if (isset($_POST['reset'])) {
            $pdo->exec("DELETE FROM leader_work_history");
            $pdo->exec("DELETE FROM leaders");
            $pdo->exec("ALTER TABLE leaders AUTO_INCREMENT = 1");
            echo "<p style='color: green;'>✅ Đã xóa dữ liệu cũ</p>";
        } else if (!isset($_POST['reset'])) {
            exit;
        }
    }
    
    // Bước 3: Thêm dữ liệu mới
    echo "<h3>➕ Bước 3: Thêm dữ liệu lãnh đạo</h3>";
    
    // Thêm Sơn Trường
    $stmt = $pdo->prepare("INSERT INTO leaders (
        name, position, image_path, birth_date, gender, ethnicity, nationality, religion,
        party_date, party_official_date, party_member_id, birth_place, hometown, residence,
        id_number, id_issue_date, id_issue_place, education, education_level, political_theory,
        state_management, language, profession, work_position, party_position, party_organization,
        responsibilities, phone, email, health_status, awards, display_order, is_active
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $stmt->execute([
        'Sơn Trường',
        'Đại biểu Hội đồng nhân dân xã Long Hiệp nhiệm kỳ 2026-2031',
        'images/leaders/son-truong.jpg',
        '01/01/1982',
        'Nam',
        'Kinh',
        'Việt Nam',
        'Không',
        '13/10/2005',
        '13/10/2006',
        '0568200154',
        'Xã Long Hiệp, Tỉnh Vĩnh Long',
        'Ấp Nhứt Tư H, Xã Hàm Giang, Tỉnh Vĩnh Long',
        'Ấp Nhứt Tư H, Xã Hàm Giang, Tỉnh Vĩnh Long',
        '0568200154',
        '08/08/2001',
        'Công an tỉnh Vĩnh Long',
        'Trung cấp chuyên nghiệp',
        '12/12 phổ thông',
        'Cử nhân Luật',
        'Học hàm',
        'Tiếng Việt',
        'Cán bộ',
        'Anh văn B, Nghề Khmer',
        'Cán bộ',
        'Thư ký trong Ủy ban Kiểm tra Đảng ủy',
        'Ủy ban Kiểm tra Đảng ủy xã Long Hiệp',
        'Phụ trách công tác kiểm tra, giám sát và thi hành kỷ luật Đảng',
        null,
        null,
        'Tốt',
        'Bằng khen của UBND tỉnh Vĩnh Long năm 2020',
        1,
        1
    ]);
    
    $sonTruongId = $pdo->lastInsertId();
    echo "<p style='color: green;'>✅ Đã thêm Sơn Trường (ID: $sonTruongId)</p>";
    
    // Thêm quá trình công tác cho Sơn Trường
    $workHistory = [
        ['Từ 05/2004 đến 05/2005', 'Công tác viên Huyện Đoàn xã Long Hiệp, Ủy ban nhân dân xã Hàm Giang', 1],
        ['Từ 05/2005 đến 12/2006', 'Phó bí thư Đoàn khối cơng tác viên Huyện Đoàn xã Hàm Giang, Ủy ban nhân dân xã Hàm Giang', 2],
        ['Từ 01/2007 đến 12/2009', 'Bí thư Đoàn khối Thanh niên xã Hàm Giang, Ủy ban nhân dân xã Hàm Giang', 3],
        ['Từ 01/2010 đến 05/2015', 'Phó Chủ tịch UBND xã Hàm Giang, Chủ tịch Hội đồng xã Hàm Giang, Ủy ban nhân dân xã Hàm Giang', 4],
        ['Từ 06/2015 đến 05/2020', 'Phó bí thư thường trực Đảng ủy xã Hàm Ký 2015 - 2020 khóa Chủ tịch HĐND xã nhiệm kỳ 2016 - 2021 Đảng ủy xã Hàm Giang', 5],
        ['Từ 06/2020 đến 06/2024', 'Phó bí thư Ủy ban kiểm tra Huyện ủy Trà Cú, nhiệm kỳ 2020 - 2025, Ủy ban Kiểm tra Huyện ủy Trà Cú', 6],
        ['Từ 07/2024 đến 12/2025', 'Chủ nhiệm Ủy ban kiểm tra Đảng ủy xã Thạnh Giang, nhiệm kỳ 2025 - 2030, Ủy ban Kiểm tra Đảng ủy xã Thạnh Giang', 7],
        ['Từ 01/2026 đến nay', 'Cán bộ Ủy ban Kiểm tra Đảng ủy xã Long Hiệp, nhiệm kỳ 2025 - 2030, Ủy ban Kiểm tra Đảng ủy xã Long Hiệp', 8]
    ];
    
    $workStmt = $pdo->prepare("INSERT INTO leader_work_history (leader_id, period, position, display_order) VALUES (?, ?, ?, ?)");
    foreach ($workHistory as $work) {
        $workStmt->execute([$sonTruongId, $work[0], $work[1], $work[2]]);
    }
    echo "<p style='color: green;'>✅ Đã thêm " . count($workHistory) . " quá trình công tác cho Sơn Trường</p>";
    
    // Thêm các lãnh đạo khác
    $otherLeaders = [
        ['Nguyễn Khánh Hòa', 'Chủ tịch UBND xã Long Hiệp', 'Phụ trách chung công tác điều hành UBND xã, quản lý kinh tế - xã hội, đầu tư xây dựng cơ bản', 2],
        ['Trần Văn Mười', 'Bí thư Đảng ủy xã Long Hiệp', 'Phụ trách công tác lãnh đạo Đảng bộ, chỉ đạo thực hiện các nghị quyết của Đảng', 3],
        ['Lê Thị Mai', 'Phó Chủ tịch UBND xã Long Hiệp', 'Phụ trách công tác văn hóa - xã hội, giáo dục - đào tạo, y tế, dân số', 4],
        ['Võ Minh Tâm', 'Chủ tịch Hội đồng nhân dân xã Long Hiệp', 'Phụ trách hoạt động của Hội đồng nhân dân, giám sát hoạt động của UBND xã', 5]
    ];
    
    $leaderStmt = $pdo->prepare("INSERT INTO leaders (name, position, image_path, responsibilities, display_order, is_active) VALUES (?, ?, ?, ?, ?, 1)");
    
    foreach ($otherLeaders as $leader) {
        $imagePath = 'images/leaders/' . strtolower(str_replace([' ', 'ă', 'â', 'ê', 'ô', 'ơ', 'ư', 'đ'], ['-', 'a', 'a', 'e', 'o', 'o', 'u', 'd'], $leader[0])) . '.jpg';
        $leaderStmt->execute([$leader[0], $leader[1], $imagePath, $leader[2], $leader[3]]);
        echo "<p style='color: green;'>✅ Đã thêm {$leader[0]}</p>";
    }
    
    // Bước 4: Kiểm tra kết quả
    echo "<h3>🎯 Bước 4: Kết quả</h3>";
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM leaders WHERE is_active = 1");
    $finalCount = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p style='color: green; font-size: 18px;'><strong>✅ Hoàn thành! Đã thêm {$finalCount['total']} lãnh đạo vào database.</strong></p>";
    
    echo "<div style='background: #f0f8ff; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h4>🎉 Thành công!</h4>";
    echo "<p>✅ Dữ liệu lãnh đạo đã được thêm vào database</p>";
    echo "<p>✅ Sơn Trường đã có trong danh sách với đầy đủ thông tin</p>";
    echo "<p>✅ Trang lãnh đạo sẽ hiển thị dữ liệu từ database</p>";
    echo "<p><strong>Bước tiếp theo:</strong></p>";
    echo "<ul>";
    echo "<li><a href='lanh-dao.php' target='_blank'>👀 Xem trang lãnh đạo</a></li>";
    echo "<li><a href='chi-tiet-lanh-dao.php?id=$sonTruongId' target='_blank'>📋 Xem chi tiết Sơn Trường</a></li>";
    echo "<li>📁 Thêm ảnh vào thư mục <code>images/leaders/</code></li>";
    echo "</ul>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Lỗi: " . $e->getMessage() . "</p>";
    echo "<p>Chi tiết lỗi:</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>