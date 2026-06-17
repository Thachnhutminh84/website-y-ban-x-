# Database Redesign — UBND Xã Long Hiệp

## Tổng quan

**Trước:** 45 bảng, nhiều trùng lặp, thiếu FK, tên cột hỗn hợp
**Sau:** ~25 bảng, chuẩn hóa, đầy đủ FK, tên cột nhất quán

## Thay đổi chính

### 1. Gộp bảng nhân sự (3 → 1)

| Bảng cũ | Trạng thái | Bảng mới |
|---------|-----------|----------|
| `department_staff` (74 dòng) | **GIỮ** — thêm cột từ hr_employees | `department_staff` |
| `hr_employees` (11 dòng) | **GỘP** vào department_staff, rồi DROP | — |
| `employee_profiles` (0 dòng) | **DROP** (không dùng) | — |

### 2. Gộp bảng HR trùng lặp

| Bảng cũ | Trạng thái | Bảng mới |
|---------|-----------|----------|
| `hr_leave` | **GỘP** vào leave_requests | `leave_requests` |
| `leave_requests` | **GIỮ** — thêm cột từ hr_leave | `leave_requests` |
| `hr_performance_evaluations` | **GỘP** vào performance_reviews | `performance_reviews` |
| `performance_reviews` | **GIỮ** — thêm cột từ hr_performance_evaluations | `performance_reviews` |
| `hr_training` | **GỘP** vào training_programs | `training_programs` |

### 3. Xóa bảng không dùng (18 bảng)

| Bảng | Lý do xóa |
|------|----------|
| `employee_profiles` | 0 dòng, đã gộp |
| `hr_employees` | Đã gộp vào department_staff |
| `hr_leave` | Đã gộp vào leave_requests |
| `hr_performance_evaluations` | Đã gộp vào performance_reviews |
| `hr_training` | Đã gộp vào training_programs |
| `attendance` | 0 dòng, không dùng |
| `payroll` | 0 dòng, không dùng |
| `galleries` | PHP không tham khảo |
| `gallery_media` | PHP không tham khảo |
| `news_media` | PHP không tham khảo |
| `hr_goals` | Không dùng |
| `hr_rewards_disciplines` | Không dùng |
| `hr_work_history` | Không dùng |
| `hr_evaluation_criteria` | Không dùng |
| `hr_evaluation_periods` | Chỉ dùng trong script fix |
| `hr_evaluation_scores` | Không dùng |
| `user_approval_history` | Không dùng |
| `pending_users` | View, không dùng |

### 4. Bảng mới cần tạo

| Bảng | Mục đích |
|------|---------|
| `remember_tokens` | JWT refresh tokens |
| `password_resets` | Quên mật khẩu |
| `settings` | Cấu hình site |

### 5. Thêm Foreign Key thiếu

| Bảng | FK thêm |
|------|--------|
| `leave_requests` | employee_id → department_staff(id) |
| `performance_reviews` | employee_id → department_staff(id) |
| `hr_bonuses` | employee_id → department_staff(id) |
| `training_participants` | employee_id → department_staff(id) |
| `documents` | department_id → departments(id) |

## Schema chi tiết

### Xem file: `database-migration.sql`
