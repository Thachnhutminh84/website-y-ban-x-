<?php
// ============================================================
// CƠ SỞ TRI THỨC CHATBOT - UBND XÃ LONG HIỆP
// ============================================================

$chatbotKnowledge = [
    // ==================== GIỚI THIỆU ====================
    [
        'keywords' => ['giới thiệu', 'long hiệp', 'xã long hiệp', 'vĩnh long', 'địa giới', 'diện tích', 'dân số', 'phường xã'],
        'response' => 'Xã Long Hiệp thuộc tỉnh Vĩnh Long. Xã có diện tích khoảng 2.085 ha, dân số khoảng 12.000 người. Xã được chia thành nhiều ấp gồm: An Hiệp, An Thạnh, Long Hiệp, Thạnh Mỹ và các ấp khác.'
    ],
    [
        'keywords' => ['lịch sử', 'thành lập', 'quá trình', 'truyền thống'],
        'response' => 'Xã Long Hiệp có lịch sử hình thành và phát triển gắn liền với quá trình khai phá vùng đất Vĩnh Long. Xã được tái lập sau năm 1976.'
    ],

    // ==================== THỦ TỤC HÀNH CHÍNH ====================
    [
        'keywords' => ['thủ tục', 'hành chính', 'tthc', 'giấy tờ', 'hồ sơ', 'lệ phí', 'nộp hồ sơ'],
        'response' => 'UBND xã đang quản lý 410 thủ tục hành chính thuộc nhiều lĩnh vực. Bạn có thể liên hệ trực tiếp UBND xã để được hướng dẫn chi tiết.'
    ],
    [
        'keywords' => ['ký sinh', 'giấy khai sinh', 'đăng ký khai sinh', 'khai sinh cho con'],
        'response' => 'Thủ tục đăng ký khai sinh: Hồ sơ gồm CMND/CCCD của cha mẹ, giấy tờ kết hôn, giấy tờ sinh. Nộp tại Văn phòng UBND xã. Thời hạn giải quyết: 5 ngày làm việc. Lệ phí: Miễn phí.'
    ],
    [
        'keywords' => ['kết hôn', 'đăng ký kết hôn', 'cưới', 'ly hôn', 'ly dị'],
        'response' => 'Đăng ký kết hôn: Nộp tại UBND xã. Hồ sơ gồm CMND/CCCD, giấy tờ chứng nhận độc thân, 4 ảnh 3x4. Thời hạn: 3 ngày làm việc. Lệ phí: 30.000đ.'
    ],
    [
        'keywords' => ['đăng ký tạm trú', 'tạm vắng', 'khai báo tạm trú', 'tạm trú'],
        'response' => 'Đăng ký tạm trú: Nộp tại Công an xã. Hồ sơ gồm CMND/CCCD, giấy tờ chứng minh chỗ ở hợp pháp. Thời hạn: 3 ngày làm việc.'
    ],
    [
        'keywords' => ['đăng ký thường trú', 'sổ hộ khẩu', 'chuyển hộ khẩu', 'nhập khẩu'],
        'response' => 'Đăng ký thường trú: Nộp tại Công an xã. Hồ sơ gồm CMND/CCCD, giấy tờ chứng minh chỗ ở hợp pháp, giấy tờ liên quan. Thời hạn: 7 ngày làm việc.'
    ],
    [
        'keywords' => ['chứng thực', 'công chứng', 'xác nhận', 'giấy xác nhận'],
        'response' => 'Dịch vụ chứng thực: Nộp tại Văn phòng UBND xã. Các loại giấy tờ cần chứng thực: CMND, CCCD, giấy tờ nhà đất, giấy tờ khác. Lệ phí: 5.000đ/trang.'
    ],
    [
        'keywords' => ['giấy phép', 'xây dựng', 'đ phép', 'đăng ký xây'],
        'response' => 'Giấy phép xây dựng: Nộp tại Phòng Kinh tế UBND xã. Hồ sơ gồm đơn đề nghị, bản vẽ thiết kế, giấy tờ quyền sử dụng đất. Thời hạn: 20 ngày làm việc.'
    ],
    [
        'keywords' => ['đất đai', 'sổ đỏ', 'sổ hồng', 'quyền sử dụng đất', 'cấp sổ đỏ', 'nhà đất'],
        'response' => 'Vấn đề đất đai: Liên hệ Phòng Kinh tế UBND xã để được hướng dẫn. Hotline: 0944.942.121 (Trưởng phòng Kim Bảy Ly).'
    ],
    [
        'keywords' => ['cấp giấy chứng nhận', 'giấy chứng nhận quyền sử dụng', 'sổ đỏ', 'sổ hồng'],
        'response' => 'Cấp giấy chứng nhận quyền sử dụng đất: Liên hệ Phòng Kinh tế. Hồ sơ gồm đơn đề nghị, bản vẽ thửa đất, giấy tờ về quyền sử dụng đất. Thời hạn: 30 ngày làm việc.'
    ],
    [
        'keywords' => ['thuế', 'nộp thuế', 'lệ phí trước bạ', 'thuế đất'],
        'response' => 'Thông tin thuế: Liên hệ Phòng Kinh tế UBND xã để được hướng dẫn chi tiết về các loại thuế, phí liên quan đến đất đai, xây dựng.'
    ],

    // ==================== LỊCH TIẾP DÂN ====================
    [
        'keywords' => ['lịch tiếp dân', 'tiếp dân', 'gặp gỡ', 'phản ánh', 'khiếu nại', 'tố cáo', 'kiến nghị'],
        'response' => 'Lịch tiếp dân định kỳ: Thứ 2 và Thứ 5 hàng tuần, từ 8:00 - 11:00 và 13:30 - 17:00 tại trụ sở UBND xã Long Hiệp. Chủ tịch UBND xã tiếp dân vào sáng Thứ 3 hàng tuần.'
    ],
    [
        'keywords' => ['khiếu nại', 'tố cáo', 'phản ánh', 'gửi kiến nghị'],
        'response' => 'Hồ sơ khiếu nại, tố cáo: Nộp tại Văn phòng UBND xã. Cần có đơn khiếu nại/tố cáo rõ ràng, kèm theo bằng chứng. Thời hạn giải quyết: 45 ngày theo quy định pháp luật.'
    ],

    // ==================== LIÊN HỆ ====================
    [
        'keywords' => ['liên hệ', 'điện thoại', 'số điện thoại', 'email', 'hotline', 'gọi', 'fax'],
        'response' => 'Thông tin liên hệ UBND xã Long Hiệp: Địa chỉ: xã Long Hiệp, tỉnh Vĩnh Long. ĐT: (0270) 3.856.417. Email: ubnd.longhiep@vinhlong.gov.vn.'
    ],
    [
        'keywords' => ['chủ tịch', 'phó chủ tịch', 'lãnh đạo ubnd', 'nguyễn khánh hòa'],
        'response' => 'Chủ tịch UBND xã: Nguyễn Khánh Hòa. ĐT: 0934.032.959. Phó Chủ tịch: Kiên Thanh Huy Sale (0384.975.899), Trần Thanh Tùng (0973.672.092).'
    ],

    // ==================== GIỜ LÀM VIỆC ====================
    [
        'keywords' => ['giờ làm việc', 'làm việc', 'thời gian', 'giờ mở cửa', 'mở cửa', 'nghỉ', 'cuối tuần', 'thứ bảy', 'chủ nhật'],
        'response' => 'Giờ làm việc UBND xã Long Hiệp: Thứ 2 - Thứ 6: 7:30 - 11:30 và 13:30 - 17:00. Thứ 7: 7:30 - 11:30. Chủ nhật: Nghỉ.'
    ],
    [
        'keywords' => ['nghỉ lễ', 'ngày lễ', 'tết', 'nghỉ tết', 'nghỉ quốc khánh', 'nghỉ 30/4', 'nghỉ 2/9'],
        'response' => 'Nghỉ lễ theo quy định nhà nước: Tết Nguyên Đán (5 ngày), 30/4 - 1/5 (5 ngày), 2/9 (1 ngày). Ngoài ra còn nghỉ các ngày lễ lớn khác theo quy định.'
    ],

    // ==================== PHÒNG BAN ====================
    [
        'keywords' => ['phòng ban', 'cơ cấu', 'tổ chức', 'bộ máy', 'đơn vị'],
        'response' => 'UBND xã Long Hiệp có 4 phòng ban chính: (1) Ủy ban Nhân dân Xã, (2) Văn phòng HĐND và UBND, (3) Phòng Kinh tế, (4) Phòng Văn hóa - Xã hội.'
    ],
    [
        'keywords' => ['văn phòng', 'văn phòng hdnd', 'văn phòng ubnd', 'nguyễn trọng thủy'],
        'response' => 'Văn phòng HĐND và UBND: Chánh Văn phòng - Nguyễn Trọng Thủy. ĐT: 0931.060.339. Chuyên quản lý: Văn phòng, Tư pháp, Đối ngoại.'
    ],
    [
        'keywords' => ['phòng kinh tế', 'kinh tế', 'kim bảy ly', 'tài chính', 'nông nghiệp', 'xây dựng'],
        'response' => 'Phòng Kinh tế: Trưởng phòng - Kim Bảy Ly. ĐT: 0944.942.121. Quản lý: Tài chính-Kế hoạch, Xây dựng-Công Thương, Nông nghiệp-Môi trường.'
    ],
    [
        'keywords' => ['phòng văn hóa', 'văn hóa xã hội', 'thạch thanh mỹ', 'giáo dục', 'y tế'],
        'response' => 'Phòng Văn hóa - Xã hội: Trưởng phòng - Thạch Thanh Mỹ. ĐT: 0343.791.397. Quản lý: Nội vụ, Giáo dục-Đào tạo, Văn hóa-Khoa học-Thông tin, Y tế.'
    ],
    [
        'keywords' => ['công an', 'an ninh', 'trật tự', 'công an xã'],
        'response' => 'Công an xã Long Hiệp: Thực hiện công tác an ninh, trật tự trên địa bàn xã. Liên hệ UBND xã để biết thông tin chi tiết.'
    ],
    [
        'keywords' => ['quân sự', 'quân đội', 'nghĩa vụ quân sự', 'đăng ký nghĩa vụ'],
        'response' => 'Quân sự xã: Thực hiện công tác quốc phòng, quân sự địa phương. Đăng ký nghĩa vụ quân sự tại Ban Chỉ huy Quân sự xã.'
    ],
    [
        'keywords' => ['trạm y tế', 'y tế', 'khám bệnh', 'bệnh viện', 'phòng khám', 'sức khỏe'],
        'response' => 'Trạm Y tế xã Long Hiệp: Khám chữa bệnh ban đầu, tiêm chủng, khám sức khỏe định kỳ. Địa chỉ: Gần trụ sở UBND xã.'
    ],

    // ==================== DỊCH VỤ CÔNG ====================
    [
        'keywords' => ['dịch vụ', 'công', 'trực tuyến', 'online', 'mạng', 'cổng dịch vụ'],
        'response' => 'UBND xã Long Hiệp cung cấp nhiều dịch vụ công trực tuyến. Bạn có thể liên hệ UBND xã để được hướng dẫn nộp hồ sơ trực tuyến.'
    ],
    [
        'keywords' => ['nộp hồ sơ trực tuyến', 'eportal', 'cổng thông tin'],
        'response' => 'Để nộp hồ sơ trực tuyến, bạn cần đăng ký tài khoản trên cổng dịch vụ công. Sau khi đăng nhập, chọn thủ tục cần nộp và làm theo hướng dẫn.'
    ],

    // ==================== TÌM KIẾM ====================
    [
        'keywords' => ['tìm kiếm', 'tra cứu', 'tìm', 'search'],
        'response' => 'Bạn có thể sử dụng ô tìm kiếm trên trang chủ hoặc truy cập các trang Dịch vụ công, Danh bạ để tìm thông tin cần thiết.'
    ],

    // ==================== ĐĂNG KÝ TÀI KHOẢN ====================
    [
        'keywords' => ['đăng ký', 'tài khoản', 'đăng nhập', 'tạo tài khoản', 'người dùng', 'đăng kí', 'mật khẩu'],
        'response' => 'Bạn có thể đăng ký tài khoản tại trang Đăng ký (dang-ky.php) để sử dụng dịch vụ trực tuyến. Nếu quên mật khẩu, vui lòng liên hệ quản trị viên.'
    ],

    // ==================== ĐỊA CHỈ ====================
    [
        'keywords' => ['địa chỉ', 'ở đâu', 'nơi nào', 'trụ sở', 'vị trí', 'map', 'bản đồ', 'google map'],
        'response' => 'Trụ sở UBND xã Long Hiệp: xã Long Hiệp, tỉnh Vĩnh Long. Xem bản đồ Google Maps: tìm "UBND xã Long Hiệp, Vĩnh Long".'
    ],

    // ==================== NGÀY LỄ, SỰ KIỆN ====================
    [
        'keywords' => ['sự kiện', 'lễ hội', 'ngày lễ', 'kỷ niệm', 'hoạt động'],
        'response' => 'UBND xã tổ chức nhiều sự kiện trong năm: Lễ kỷ niệm ngày thành lập xã, các ngày lễ lớn, phong trào toàn dân đoàn kết xây dựng đời sống văn hóa. Theo dõi trang Tin tức để cập nhật.'
    ],

    // ==================== GIÁO DỤC ====================
    [
        'keywords' => ['giáo dục', 'trường học', 'mầm non', 'tiểu học', 'trung học', 'học sinh', 'giáo viên'],
        'response' => 'Phòng Văn hóa - Xã hội quản lý lĩnh vực giáo dục trên địa bàn xã. Liên hệ Phòng VH-XH: 0343.791.397 để biết thông tin về trường học, giáo dục.'
    ],

    // ==================== Y TẾ ====================
    [
        'keywords' => ['tiêm chủng', 'vắc xin', 'khám sức khỏe', 'dịch bệnh', 'phòng bệnh'],
        'response' => 'Trạm Y tế xã Long Hiệp thực hiện tiêm chủng định kỳ, khám sức khỏe, phòng chống dịch bệnh. Liên hệ Trạm Y tế hoặc Phòng VH-XH: 0343.791.397.'
    ],

    // ==================== NÔNG NGHIỆP ====================
    [
        'keywords' => ['nông nghiệp', 'nông dân', 'trồng trọt', 'chăn nuôi', 'khuyến nông', 'lúa', 'cây ăn trái'],
        'response' => 'Phòng Kinh tế hỗ trợ phát triển nông nghiệp: khuyến nông, kỹ thuật trồng trọt, chăn nuôi, phòng chống dịch bệnh cây trồng vật nuôi. ĐT: 0944.942.121.'
    ],

    // ==================== MÔI TRƯỜNG ====================
    [
        'keywords' => ['môi trường', 'rác', 'vệ sinh', 'ô nhiễm', 'xả rác', 'môi trường sống'],
        'response' => 'Phòng Kinh tế quản lý lĩnh vực môi trường. Vui lòng liên hệ để phản ánh các vấn đề về môi trường, vệ sinh trên địa bàn xã.'
    ],

    // ==================== AN NINH ====================
    [
        'keywords' => ['an ninh', 'trật tự', 'trộm cắp', 'cướp', 'tội phạm', 'bảo vệ'],
        'response' => 'Phòng An ninh trật tự xã thực hiện công tác phòng chống tội phạm, bảo vệ an ninh trật tự. Khi phát hiện tội phạm, gọi ngay Công an xã hoặc tổng đài 113.'
    ],

    // ==================== DÂN SỐ ====================
    [
        'keywords' => ['dân số', 'kế hoạch hóa gia đình', 'sinh con', 'giám định thai nghén'],
        'response' => 'Công tác dân số và kế hoạch hóa gia đình do Phòng Văn hóa - Xã hội phụ trách. Liên hệ: 0343.791.397.'
    ],

    // ==================== BẢO TRỢ XÃ HỘI ====================
    [
        'keywords' => ['bảo trợ', 'người cao tuổi', 'trẻ em', 'khuyết tật', 'nghèo', 'hộ nghèo', 'an sinh'],
        'response' => 'Chính sách bảo trợ xã hội: Hỗ trợ người cao tuổi, trẻ em khuyết tật, hộ nghèo, cận nghèo. Liên hệ Phòng VH-XH: 0343.791.397 để được hướng dẫn.'
    ],

    // ==================== HỎI - ĐÁP ====================
    [
        'keywords' => ['hỏi', 'đáp', 'câu hỏi', 'thắc mắc', 'giải đáp'],
        'response' => 'Bạn có thể gửi câu hỏi tại đây hoặc liên hệ trực tiếp UBND xã. Chúng tôi sẽ phản hồi trong thời gian sớm nhất!'
    ],

    // ==================== GỢI Ý ====================
    [
        'keywords' => ['gợi ý', 'hướng dẫn', 'chỉ dẫn', 'help'],
        'response' => 'Bạn có thể hỏi tôi về: Thủ tục hành chính, Lịch tiếp dân, Giờ làm việc, Liên hệ, Phòng ban, Dịch vụ công, Địa chỉ, Thông tin xã Long Hiệp.'
    ],

    // ==================== CẢM ƠN ====================
    [
        'keywords' => ['cảm ơn', 'thanks', 'thank', 'cám ơn', 'ok', 'tốt', 'hay'],
        'response' => 'Rất vui được hỗ trợ bạn! Nếu có câu hỏi khác, đừng ngần ngại hỏi nhé.'
    ],

    // ==================== XIN CHÀO ====================
    [
        'keywords' => ['xin chào', 'chào', 'hello', 'hi', 'hey', 'alo', 'chào bạn'],
        'response' => 'Xin chào! Tôi là trợ lý ảo của UBND Xã Long Hiệp. Tôi có thể giúp bạn tìm thông tin về thủ tục hành chính, lịch tiếp dân, liên hệ, giờ làm việc và nhiều thông tin khác.'
    ],

    // ==================== TỔNG ĐÀI ====================
    [
        'keywords' => ['tổng đài', 'cấp cứu', '113', '114', '115', 'khẩn cấp'],
        'response' => 'Tổng đài khẩn cấp: 113 (Công an), 114 (PCCC), 115 (Cấp cứu). Liên hệ UBND xã: (0270) 3.856.417.'
    ],

    // ==================== QUYẾT ĐỊNH ====================
    [
        'keywords' => ['quyết định', 'nghị quyết', 'văn bản', 'quy định'],
        'response' => 'Các quyết định, nghị quyết của UBND xã được công bố trên cổng thông tin điện tử. Bạn có thể xem tại trang Văn bản quy phạm.'
    ],

    // ==================== ĐƠNG TỤC ====================
    [
        'keywords' => ['đơn', 'đơn từ', 'đơn đề nghị', 'đơn xin'],
        'response' => 'Các loại đơn thường dùng: Đơn đề nghị cấp giấy phép, đơn khiếu nại, đơn tố cáo, đơn xin xác nhận. Nộp tại Văn phòng UBND xã.'
    ],

    // ==================== THÔNG TIN CHUNG ====================
    [
        'keywords' => ['thông tin', 'chung', 'tổng quát', 'tổng hợp'],
        'response' => 'UBND xã Long Hiệp hoạt động phục vụ nhân dân trên địa bàn xã. Website: https://longhiep.vinhlong.gov.vn để cập nhật thông tin mới nhất.'
    ],

    // ==================== BẢO HIỂM XÃ HỘI ====================
    [
        'keywords' => ['bảo hiểm', 'bhxh', 'bảo hiểm xã hội', 'bảo hiểm y tế', 'bhyt', 'thẻ bảo hiểm'],
        'response' => 'Bảo hiểm xã hội: Liên hệ Phòng Văn hóa - Xã hội để được hướng dẫn办理 các chế độ BHXH, BHYT. ĐT: 0343.791.397.'
    ],

    // ==================== NGHỈ PHÉP ====================
    [
        'keywords' => ['nghỉ phép', 'phép năm', 'nghỉ nghỉ', 'nghỉ việc'],
        'response' => 'Chính sách nghỉ phép: Công chức được nghỉ phép năm theo quy định. Liên hệ Phòng VH-XH để biết chi tiết.'
    ],

    // ==================== LƯƠNG ====================
    [
        'keywords' => ['lương', 'tiền lương', 'thưởng', 'phụ cấp'],
        'response' => 'Thông tin lương, thưởng, phụ cấp: Liên hệ Phòng VH-XH hoặc Bộ phận Tài chính để được hỗ trợ.'
    ],

    // ==================== CÁN BỘ ====================
    [
        'keywords' => ['cán bộ', 'công chức', 'nhân viên', 'nhân sự'],
        'response' => 'UBND xã Long Hiệp có khoảng 30 cán bộ, công chức hoạt động tại 4 phòng ban. Xem danh sách tại trang Tổ chức nhân sự.'
    ],

    // ==================== HỘ GIA ĐÌNH ====================
    [
        'keywords' => ['hộ gia đình', 'hộ khẩu', 'gia đình', 'hộ nghèo', 'hộ cận nghèo'],
        'response' => 'Chính sách hộ gia đình: Hỗ trợ hộ nghèo, cận nghèo. Liên hệ Phòng VH-XH để được hướng dẫn làm hồ sơ.'
    ],

    // ==================== TRẺ EM ====================
    [
        'keywords' => ['trẻ em', 'trẻ nhỏ', 'em bé', 'nuôi dạy', 'trẻ mồ côi'],
        'response' => 'Chính sách trẻ em: Bảo trợ trẻ em mồ côi, khuyết tật, trẻ em có hoàn cảnh khó khăn. Liên hệ Phòng VH-XH: 0343.791.397.'
    ],

    // ==================== NGƯỜI CAO TUỔI ====================
    [
        'keywords' => ['người cao tuổi', 'người già', 'già', 'cao tuổi'],
        'response' => 'Chế độ người cao tuổi: Trợ cấp, chăm sóc sức khỏe, thăm hỏi. Liên hệ Phòng VH-XH để được hỗ trợ.'
    ],

    // ==================== PHỤ NỮ ====================
    [
        'keywords' => ['phụ nữ', 'chị em', 'nữ', 'bình đẳng giới'],
        'response' => 'Chính sách phụ nữ: Bình đẳng giới, phòng chống bạo lực gia đình, hỗ trợ phụ nữ phát triển kinh tế. Liên hệ Hội Phụ nữ xã.'
    ],

    // ==================== THANH NIÊN ====================
    [
        'keywords' => ['thanh niên', 'trẻ', 'tuổi trẻ', 'đoàn thanh niên'],
        'response' => 'Phong trào thanh niên: Hoạt động Đoàn Thanh niên, tình nguyện, phát triển kinh tế. Liên hệ Đoàn Thanh niên xã.'
    ],

    // ==================== GIÁO DỤC ====================
    [
        'keywords' => ['giáo dục', 'trường học', 'mầm non', 'tiểu học', 'trung học', 'học sinh', 'giáo viên'],
        'response' => 'Phòng Văn hóa - Xã hội quản lý lĩnh vực giáo dục trên địa bàn xã. Liên hệ Phòng VH-XH: 0343.791.397 để biết thông tin về trường học, giáo dục.'
    ],

    // ==================== Y TẾ ====================
    [
        'keywords' => ['tiêm chủng', 'vắc xin', 'khám sức khỏe', 'dịch bệnh', 'phòng bệnh'],
        'response' => 'Trạm Y tế xã Long Hiệp thực hiện tiêm chủng định kỳ, khám sức khỏe, phòng chống dịch bệnh. Liên hệ Trạm Y tế hoặc Phòng VH-XH: 0343.791.397.'
    ],

    // ==================== NÔNG NGHIỆP ====================
    [
        'keywords' => ['nông nghiệp', 'nông dân', 'trồng trọt', 'chăn nuôi', 'khuyến nông', 'lúa', 'cây ăn trái'],
        'response' => 'Phòng Kinh tế hỗ trợ phát triển nông nghiệp: khuyến nông, kỹ thuật trồng trọt, chăn nuôi, phòng chống dịch bệnh cây trồng vật nuôi. ĐT: 0944.942.121.'
    ],

    // ==================== MÔI TRƯỜNG ====================
    [
        'keywords' => ['môi trường', 'rác', 'vệ sinh', 'ô nhiễm', 'xả rác', 'môi trường sống'],
        'response' => 'Phòng Kinh tế quản lý lĩnh vực môi trường. Vui lòng liên hệ để phản ánh các vấn đề về môi trường, vệ sinh trên địa bàn xã.'
    ],

    // ==================== AN NINH ====================
    [
        'keywords' => ['an ninh', 'trật tự', 'trộm cắp', 'cướp', 'tội phạm', 'bảo vệ'],
        'response' => 'Phòng An ninh trật tự xã thực hiện công tác phòng chống tội phạm, bảo vệ an ninh trật tự. Khi phát hiện tội phạm, gọi ngay Công an xã hoặc tổng đài 113.'
    ],

    // ==================== DÂN SỐ ====================
    [
        'keywords' => ['dân số', 'kế hoạch hóa gia đình', 'sinh con', 'giám định thai nghén'],
        'response' => 'Công tác dân số và kế hoạch hóa gia đình do Phòng Văn hóa - Xã hội phụ trách. Liên hệ: 0343.791.397.'
    ],

    // ==================== BẢO TRỢ XÃ HỘI ====================
    [
        'keywords' => ['bảo trợ', 'người cao tuổi', 'trẻ em', 'khuyết tật', 'nghèo', 'hộ nghèo', 'an sinh'],
        'response' => 'Chính sách bảo trợ xã hội: Hỗ trợ người cao tuổi, trẻ em khuyết tật, hộ nghèo, cận nghèo. Liên hệ Phòng VH-XH: 0343.791.397 để được hướng dẫn.'
    ],

    // ==================== THUẾ ====================
    [
        'keywords' => ['thuế', 'nộp thuế', 'lệ phí', 'phí', 'thuế đất', 'thuế nhà'],
        'response' => 'Thông tin thuế, phí: Liên hệ Phòng Kinh tế UBND xã để được hướng dẫn về các loại thuế, phí liên quan đến đất đai, xây dựng.'
    ],

    // ==================== NGÂN HÀNG ====================
    [
        'keywords' => ['ngân hàng', 'tiết kiệm', 'vay vốn', 'tín dụng'],
        'response' => 'Thông tin ngân hàng: Liên hệ Phòng Kinh tế để được hỗ trợ vay vốn ưu đãi, programs phát triển kinh tế.'
    ],

    // ==================== GIAO THÔNG ====================
    [
        'keywords' => ['giao thông', 'đường', 'cầu', 'đường bộ', 'tai nạn'],
        'response' => 'Phòng Kinh tế quản lý hạ tầng giao thông. Phản ánh tình trạng đường sá, cầu cống tại UBND xã.'
    ],

    // ==================== ĐIỆN NƯỚC ====================
    [
        'keywords' => ['điện', 'nước', 'điện lực', 'cấp nước', 'thoát nước'],
        'response' => 'Liên hệ Điện lực, Công ty nước để được hỗ trợ về dịch vụ điện, nước trên địa bàn xã.'
    ],

    // ==================== HỘ TỊCH ====================
    [
        'keywords' => ['hộ tịch', 'giấy khai sinh', 'giấy tờ hộ tịch', 'sổ hộ tịch'],
        'response' => 'Hộ tịch: Đăng ký khai sinh, kết hôn, khai tử, sửa đổi hộ tịch. Nộp tại Văn phòng UBND xã.'
    ],

    // ==================== CHỨNG THỰC ====================
    [
        'keywords' => ['chứng thực', 'công chứng', 'xác nhận chữ ký', 'sao y'],
        'response' => 'Dịch vụ chứng thực: Nộp tại Văn phòng UBND xã. Lệ phí: 5.000đ/trang. Thời hạn: Trong ngày.'
    ],

    // ==================== ĐĂNG KÝ KINH DOANH ====================
    [
        'keywords' => ['kinh doanh', 'đăng ký kinh doanh', 'doanh nghiệp', 'cửa hàng', 'tiệm'],
        'response' => 'Đăng ký kinh doanh: Nộp tại Phòng Kinh tế. Hồ sơ gồm đơn đề nghị, CMND/CCCD, giấy tờ quyền sử dụng đất. Thời hạn: 5 ngày làm việc.'
    ],

    // ==================== XÂY DỰNG ====================
    [
        'keywords' => ['xây nhà', 'xây dựng', 'sửa nhà', 'cải tạo', 'phá dỡ'],
        'response' => 'Giấy phép xây dựng: Nộp tại Phòng Kinh tế. Hồ sơ gồm đơn đề nghị, bản vẽ thiết kế, giấy tờ quyền sử dụng đất. Thời hạn: 20 ngày làm việc.'
    ],

    // ==================== MUA BÁN ĐẤT ====================
    [
        'keywords' => ['mua đất', 'bán đất', 'chuyển nhượng', 'sang tên', 'tách thửa'],
        'response' => 'Mua bán, chuyển nhượng đất: Liên hệ Phòng Kinh tế để được hướng dẫn thủ tục sang tên, tách thửa, cấp giấy chứng nhận mới.'
    ],

    // ==================== CHO THUÊ NHÀ ====================
    [
        'keywords' => ['cho thuê', 'thuê nhà', 'thuê phòng', 'nhà trọ'],
        'response' => 'Cho thuê nhà: Liên hệ Phòng Kinh tế để đăng ký và thực hiện nghĩa vụ thuế theo quy định.'
    ],

    // ==================== SỞ HỮU TRÍ TUỆ ====================
    [
        'keywords' => ['sở hữu trí tuệ', 'bản quyền', 'logo', 'thương hiệu'],
        'response' => 'Sở hữu trí tuệ: Liên hệ Phòng Kinh tế để được hướng dẫn đăng ký nhãn hiệu, bản quyền.'
    ],

    // ==================== THANH TRA ====================
    [
        'keywords' => ['thanh tra', 'kiểm tra', 'xử phạt', 'vi phạm'],
        'response' => 'Thanh tra UBND xã thực hiện kiểm tra, xử lý vi phạm hành chính trên địa bàn. Liên hệ UBND xã để phản ánh.'
    ],

    // ==================== Khiếu nại ====================
    [
        'keywords' => ['khiếu nại', 'tố cáo', 'phản ánh', 'góp ý', 'kiến nghị'],
        'response' => 'Khiếu nại, tố cáo: Nộp đơn tại Văn phòng UBND xã. Cần có đơn rõ ràng, kèm bằng chứng. Thời hạn giải quyết: 45 ngày.'
    ],

    // ==================== DỊCH VỤ CÔNG TRỰC TUYẾN ====================
    [
        'keywords' => ['dịch vụ công', 'công trực tuyến', 'nộp hồ sơ online', 'eportal'],
        'response' => 'Dịch vụ công trực tuyến: Liên hệ UBND xã để được hướng dẫn nộp hồ sơ trực tuyến.'
    ],

    // ==================== CÁC NGÀY LỄ LỚN ====================
    [
        'keywords' => ['30/4', '1/5', '2/9', 'tết', 'nguyên đán', 'quốc khánh'],
        'response' => 'Nghỉ lễ: 30/4-1/5 (5 ngày), 2/9 (1 ngày), Tết Nguyên Đán (5 ngày). Chi tiết xem lịch nghỉ lễ hàng năm.'
    ],

    // ==================== SỐ ĐIỆN THOẠI QUAN TRỌNG ====================
    [
        'keywords' => ['số điện thoại', 'hotline', 'gọi', 'liên hệ nhanh'],
        'response' => 'Số điện thoại quan trọng:\n- UBND xã: (0270) 3.856.417\n- Công an xã: 113\n- Trạm Y tế: Liên hệ UBND\n- Phòng Kinh tế: 0944.942.121\n- Phòng VH-XH: 0343.791.397'
    ],

    // ==================== HƯỚNG Dẫn ====================
    [
        'keywords' => ['hướng dẫn', 'chỉ dẫn', 'làm sao', 'thế nào', 'như thế nào'],
        'response' => 'Bạn cần hướng dẫn cụ thể về vấn đề gì? Ví dụ: Thủ tục hành chính, đất đai, xây dựng, hộ tịch, bảo hiểm...?'
    ],

    // ==================== CÓ THỂ ====================
    [
        'keywords' => ['có thể', 'có khả năng', 'có được', 'được không'],
        'response' => 'Bạn có thể hỏi tôi về hầu hết các thông tin liên quan đến UBND xã Long Hiệp. Hãy thử đặt câu hỏi cụ thể!'
    ],

    // ==================== TẠI SAO ====================
    [
        'keywords' => ['tại sao', 'vì sao', 'lý do'],
        'response' => 'Bạn có thể giải thích rõ hơn về vấn đề bạn muốn tìm hiểu không? Tôi sẽ cố gắng trả lời tốt nhất có thể.'
    ],

    // ==================== KHI NÀO ====================
    [
        'keywords' => ['khi nào', 'bao giờ', 'thời gian', 'lúc nào'],
        'response' => 'Bạn muốn biết thông tin về thời gian cụ thể nào? Ví dụ: Giờ làm việc, lịch tiếp dân, thời hạn giải quyết thủ tục...?'
    ],

    // ==================== Ở ĐÂU ====================
    [
        'keywords' => ['ở đâu', 'nơi nào', 'địa điểm', 'chỗ nào'],
        'response' => 'UBND xã Long Hiệp: xã Long Hiệp, tỉnh Vĩnh Long. ĐT: (0270) 3.856.417.'
    ],

    // ==================== BAO NHIÊU ====================
    [
        'keywords' => ['bao nhiêu', 'số lượng', 'mức', 'giá'],
        'response' => 'Bạn muốn biết thông tin cụ thể về số lượng, mức giá nào? Ví dụ: Lệ phí thủ tục, số cán bộ, diện tích...?'
    ],

    // ==================== AI ====================
    [
        'keywords' => ['ai', 'người nào', 'ai đó'],
        'response' => 'Bạn muốn biết thông tin về ai cụ thể? Ví dụ: Lãnh đạo UBND, trưởng phòng ban, cán bộ phụ trách...?'
    ],
];

// Hàm tìm câu trả lời từ CSDL tri thức
function chatbotFindAnswer($userMessage) {
    global $chatbotKnowledge;
    
    $lower = mb_strtolower($userMessage, 'UTF-8');
    
    // Tìm kiếm chính xác trước
    foreach ($chatbotKnowledge as $item) {
        foreach ($item['keywords'] as $keyword) {
            if (mb_strtolower($keyword, 'UTF-8') === $lower) {
                return $item['response'];
            }
        }
    }
    
    // Fuzzy matching - tìm keyword chứa trong câu hỏi
    $bestMatch = null;
    $bestScore = 0;
    
    foreach ($chatbotKnowledge as $item) {
        foreach ($item['keywords'] as $keyword) {
            $keywordLower = mb_strtolower($keyword, 'UTF-8');
            if (mb_strpos($lower, $keywordLower, 0, 'UTF-8') !== false) {
                $score = mb_strlen($keyword, 'UTF-8');
                if ($score > $bestScore) {
                    $bestScore = $score;
                    $bestMatch = $item['response'];
                }
            }
        }
    }
    
    if ($bestMatch) {
        return $bestMatch;
    }
    
    // Kiểm tra từng từ trong câu hỏi
    $words = preg_split('/\s+/', $lower);
    foreach ($words as $word) {
        if (mb_strlen($word, 'UTF-8') < 2) continue;
        foreach ($chatbotKnowledge as $item) {
            foreach ($item['keywords'] as $keyword) {
                $keywordLower = mb_strtolower($keyword, 'UTF-8');
                if (mb_strpos($keywordLower, $word, 0, 'UTF-8') !== false || 
                    mb_strpos($word, $keywordLower, 0, 'UTF-8') !== false) {
                    return $item['response'];
                }
            }
        }
    }
    
    return null; // Không tìm thấy trong CSDL
}
