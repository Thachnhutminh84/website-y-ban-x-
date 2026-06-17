# ============================================================
# API ARCHITECTURE - UBND Xa Long Hiep
# Spring Boot REST API Design
# ============================================================
# Base URL: http://localhost:8080/api/v1
# Auth: JWT Bearer Token
# ============================================================

# -----------------------------------------------------------
# 1. AUTHENTICATION - Xac thuc
# -----------------------------------------------------------

POST   /auth/register          # Dang ky tai khoan moi
POST   /auth/login             # Dang nhap, tra ve JWT
POST   /auth/refresh           # Lam moi token
POST   /auth/logout            # Dang xuat

# Request: POST /auth/login
# {
#   "username": "admin",
#   "password": "123456"
# }
# Response:
# {
#   "token": "eyJhbGciOi...",
#   "refreshToken": "eyJhbGciOi...",
#   "user": {
#     "id": 1,
#     "username": "admin",
#     "fullName": "Admin",
#     "role": "admin",
#     "userType": "can_bo"
#   }
# }

# -----------------------------------------------------------
# 2. USERS - Quan ly nguoi dung
# -----------------------------------------------------------

GET    /users                  # Danh sach (phan trang)
GET    /users/{id}             # Chi tiet nguoi dung
POST   /users                  # Tao nguoi dung (admin)
PUT    /users/{id}             # Cap nhat nguoi dung
DELETE /users/{id}             # Xoa nguoi dung (admin)
PUT    /users/{id}/status      # Kich hoat/Vo hieu hoa
PUT    /users/{id}/role        # Doi vai tro
GET    /users/pending          # Danh sach cho duyet
PUT    /users/{id}/approve     # Duyet nguoi dung
PUT    /users/{id}/reject      # Tu choi nguoi dung

# Response: GET /users?page=0&size=20
# {
#   "content": [{ "id": 1, "username": "admin", ... }],
#   "totalElements": 100,
#   "totalPages": 5,
#   "number": 0
# }

# -----------------------------------------------------------
# 3. DEPARTMENTS - Phong ban
# -----------------------------------------------------------

GET    /departments                        # Danh sach phong ban
GET    /departments/{slug}                 # Chi tiet theo slug
GET    /departments/{id}/staff             # Nhan vien trong phong ban
POST   /departments                        # Them phong ban
PUT    /departments/{id}                   # Cap nhat phong ban
DELETE /departments/{id}                   # Xoa phong ban

# Response: GET /departments
# [
#   {
#     "id": 1,
#     "name": "Phong Chinh phu",
#     "slug": "phong-chinh-phu",
#     "shortName": "PCP",
#     "phoneNumbers": "0270.3822...",
#     "email": "pcp@ubndlonghiep.vn",
#     "staffCount": 8,
#     "leader": { "id": 5, "fullName": "Nguyen Van A" }
#   }
# ]

# -----------------------------------------------------------
# 4. STAFF - Can bo / Nhan vien
# -----------------------------------------------------------

GET    /staff                             # Danh sach can bo (phan trang)
GET    /staff/{id}                        # Chi tiet can bo
POST   /staff                             # Them can bo
PUT    /staff/{id}                        # Cap nhat can bo
DELETE /staff/{id}                        # Xoa can bo
GET    /staff/search?q=keyword            # Tim kiem can bo
GET    /staff/department/{deptId}         # Can bo theo phong ban

# Response: GET /staff?page=0&size=20&departmentId=1
# {
#   "content": [
#     {
#       "id": 1,
#       "fullName": "Nguyen Van A",
#       "position": "Truong phong",
#       "phoneNumber": "0901234567",
#       "department": { "id": 1, "name": "Phong Chinh phu" }
#     }
#   ]
# }

# -----------------------------------------------------------
# 5. LEADERSHIP - Ban lanh dao
# -----------------------------------------------------------

GET    /leaders                           # Danh sach lanh dao
GET    /leaders/{id}                      # Chi tiet lanh dao
POST   /leaders                           # Them lanh dao
PUT    /leaders/{id}                      # Cap nhat lanh dao
DELETE /leaders/{id}                      # Xoa lanh dao
GET    /leaders/{id}/work-history         # Qua trinh cong tac

# Response: GET /leaders
# [
#   {
#     "id": 1,
#     "fullName": "Tran Van B",
#     "position": "Chu tich UBND",
#     "imageUrl": "/uploads/leaders/b.jpg",
#     "birthDate": "1975-03-15",
#     "partyMemberDate": "1998-06-20",
#     "responsibilities": "Chu tri hoat dong UBND..."
#   }
# ]

# -----------------------------------------------------------
# 6. NEWS - Tin tuc / Su kien / Thong bao
# -----------------------------------------------------------

GET    /news                              # Danh sach tin tuc (phan trang)
GET    /news/{slug}                       # Chi tiet tin tuc
POST   /news                              # Tao tin tuc
PUT    /news/{id}                         # Cap nhat tin tuc
DELETE /news/{id}                         # Xoa tin tuc
GET    /news/featured                     # Tin tuc noi bat
GET    /news/category/{categorySlug}      # Tin theo danh muc
GET    /news/search?q=keyword             # Tim kiem tin tuc
POST   /news/{id}/view                    # Tang luot xem
GET    /news/latest?limit=10              # Tin moi nhat

# Query params: ?page=0&size=20&type=tin_tuc&status=published
# Types: tin_tuc, su_kien, thong_bao
# Status: draft, published, archived

# Response: GET /news?category=thong-tin-tuyen-truyen&page=0
# {
#   "content": [
#     {
#       "id": 1,
#       "title": "Hop ba nhan thong tin...",
#       "slug": "hop-ba-nhan-thong-tin",
#       "summary": "Ngay 15/6/2026...",
#       "thumbnail": "/uploads/news/thumb.jpg",
#       "type": "tin_tuc",
#       "category": "thong-tin-tuyen-truyen",
#       "viewCount": 150,
#       "author": { "id": 1, "fullName": "Admin" },
#       "createdAt": "2026-06-15T08:00:00"
#     }
#   ]
# }

# -----------------------------------------------------------
# 7. CATEGORIES - Danh muc tin tuc
# -----------------------------------------------------------

GET    /categories                        # Tat ca danh muc
GET    /categories/{slug}                 # Chi tiet danh muc
POST   /categories                        # Them danh muc
PUT    /categories/{id}                   # Cap nhat danh muc
DELETE /categories/{id}                   # Xoa danh muc

# -----------------------------------------------------------
# 8. DOCUMENTS - Van ban
# -----------------------------------------------------------

GET    /documents                         # Danh sach van ban (phan trang)
GET    /documents/{id}                    # Chi tiet van ban
POST   /documents                         # Tao van ban
PUT    /documents/{id}                    # Cap nhat van ban
DELETE /documents/{id}                    # Xoa van ban
GET    /documents/type/{typeId}           # Van ban theo loai
GET    /documents/search?q=keyword        # Tim kiem van ban
GET    /documents/latest?limit=10         # Van ban moi nhat

# Query params: ?page=0&size=20&typeId=1&status=published&direction=incoming
# Direction: incoming (den), outgoing (di)

# Response: GET /documents
# {
#   "content": [
#     {
#       "id": 1,
#       "documentNumber": "QD-01/2026",
#       "title": "Quyet dinh ve viec...",
#       "typeName": "Quyet dinh",
#       "issuedDate": "2026-06-15",
#       "effectiveDate": "2026-06-20",
#       "status": "published",
#       "filePath": "/uploads/docs/qd-01.pdf"
#     }
#   ]
# }

# -----------------------------------------------------------
# 9. DOCUMENT TYPES - Loai van ban
# -----------------------------------------------------------

GET    /document-types                    # Tat ca loai van ban
GET    /document-types/{id}               # Chi tiet loai

# -----------------------------------------------------------
# 10. VIDEOS - Quan ly video
# -----------------------------------------------------------

GET    /videos                            # Danh sach video (phan trang)
GET    /videos/{id}                       # Chi tiet video
POST   /videos                            # Them video
PUT    /videos/{id}                       # Cap nhat video
DELETE /videos/{id}                       # Xoa video
GET    /videos/featured                   # Video noi bat
GET    /videos/album/{albumId}            # Video theo album
GET    /videos/search?q=keyword           # Tim kiem video
POST   /videos/{id}/view                  # Tang luot xem

# Types: youtube, local, vimeo

# -----------------------------------------------------------
# 11. VIDEO ALBUMS - Album video
# -----------------------------------------------------------

GET    /video-albums                      # Danh sach album
GET    /video-albums/{id}                 # Chi tiet album
POST   /video-albums                      # Them album
PUT    /video-albums/{id}                 # Cap nhat album
DELETE /video-albums/{id}                 # Xoa album

# -----------------------------------------------------------
# 12. MEDIA - Quan ly hinh anh / tai lieu
# -----------------------------------------------------------

GET    /media                             # Danh sach media (phan trang)
GET    /media/{id}                        # Chi tiet media
POST   /media/upload                      # Upload media
PUT    /media/{id}                        # Cap nhat media
DELETE /media/{id}                        # Xoa media

# Query params: ?type=image&status=active&page=0

# -----------------------------------------------------------
# 13. GALLERIES - Album hinh anh
# -----------------------------------------------------------

GET    /galleries                         # Danh sach gallery
GET    /galleries/{id}                    # Chi tiet gallery
POST   /galleries                         # Them gallery
PUT    /galleries/{id}                    # Cap nhat gallery
DELETE /galleries/{id}                    # Xoa gallery
GET    /galleries/{id}/media              # Anh trong gallery

# -----------------------------------------------------------
# 14. CONTACTS - Lien he / Phan hoi
# -----------------------------------------------------------

POST   /contacts                          # Gui phan hoi (public)
GET    /contacts                          # Danh sach lien he (admin)
GET    /contacts/{id}                     # Chi tiet lien he
PUT    /contacts/{id}/status              # Cap nhat trang thai
PUT    /contacts/{id}/note                # Them ghi chu admin
GET    /contacts/lookup?code=LH-001&email=a@b.com  # Tra cuu (public)

# Status: new, processing, resolved

# Response: POST /contacts
# {
#   "fullName": "Nguyen Van C",
#   "email": "c@gmail.com",
#   "phone": "0912345678",
#   "subject": "Phan hoi ve dich vu",
#   "message": "Toi muon hoi ve..."
# }
# Response:
# {
#   "ticketCode": "LH-00123",
#   "message": "Da gui thanh cong"
# }

# -----------------------------------------------------------
# 15. CHAT - Tin nhan noi bo
# -----------------------------------------------------------

POST   /chat/messages                     # Gui tin nhan
GET    /chat/messages                     # Lay tin nhan moi (polling)
GET    /chat/messages/history             # Lay lich su tin nhan
GET    /chat/online                       # Danh sach dang online
PUT    /chat/messages/{id}/read           # Danh dau da doc

# Request: POST /chat/messages
# { "receiverId": 2, "message": "Xin chao" }

# -----------------------------------------------------------
# 16. ADMINISTRATIVE PROCEDURES - Thu tuc hanh chinh
# -----------------------------------------------------------

GET    /procedures                        # Danh sach thu tuc
GET    /procedures/{id}                   # Chi tiet thu tuc
POST   /procedures                        # Them thu tuc
PUT    /procedures/{id}                   # Cap nhat thu tuc
DELETE /procedures/{id}                   # Xoa thu tuc

# -----------------------------------------------------------
# 17. FORMS - Mau / Tep tin tai ve
# -----------------------------------------------------------

GET    /forms                             # Danh sach form
GET    /forms/{id}                        # Chi tiet form
POST   /forms                             # Them form
PUT    /forms/{id}                        # Cap nhat form
DELETE /forms/{id}                        # Xoa form
POST   /forms/{id}/download               # Tai ve (tang download_count)

# -----------------------------------------------------------
# 18. HR - Nhan su
# -----------------------------------------------------------

GET    /hr/employees                      # Danh sach nhan vien
GET    /hr/employees/{id}                 # Chi tiet nhan vien
POST   /hr/employees                      # Them nhan vien
PUT    /hr/employees/{id}                 # Cap nhat nhan vien
DELETE /hr/employees/{id}                 # Xoa nhan vien

# -----------------------------------------------------------
# 19. HR EVALUATIONS - Danh gia nhan vien
# -----------------------------------------------------------

GET    /hr/evaluations                    # Danh sach danh gia
GET    /hr/evaluations/{id}               # Chi tiet danh gia
POST   /hr/evaluations                    # Tao danh gia
PUT    /hr/evaluations/{id}               # Cap nhat danh gia
DELETE /hr/evaluations/{id}               # Xoa danh gia
GET    /hr/evaluations/period/{periodId}  # Danh gia theo ky

# -----------------------------------------------------------
# 20. HR LEAVE - Nghi phep
# -----------------------------------------------------------

GET    /hr/leave-requests                 # Danh sach don nghi phep
POST   /hr/leave-requests                 # Gui don nghi phep
PUT    /hr/leave-requests/{id}/approve    # Duyet don
PUT    /hr/leave-requests/{id}/reject     # Tu choi don

# -----------------------------------------------------------
# 21. SALARY - Luong
# -----------------------------------------------------------

GET    /salary/payroll?month=6&year=2026         # Bang luong
POST   /salary/calculate                          # Tinh luong
GET    /salary/statistics?year=2026               # Thong ke luong

# -----------------------------------------------------------
# 22. BONUS - Khen thuong
# -----------------------------------------------------------

GET    /bonus                              # Danh sach khen thuong
POST   /bonus                              # Them khen thuong
DELETE /bonus/{id}                         # Xoa khen thuong

# -----------------------------------------------------------
# 23. PHONE DIRECTORY - Danh ba dien thoai
# -----------------------------------------------------------

GET    /phone-directory                    # Danh ba (phan trang)
GET    /phone-directory/{id}               # Chi tiet
POST   /phone-directory                    # Them
PUT    /phone-directory/{id}               # Cap nhat
DELETE /phone-directory/{id}               # Xoa
GET    /phone-directory/search?q=keyword   # Tim kiem

# -----------------------------------------------------------
# 24. SEARCH - Tim kiem toan bo
# -----------------------------------------------------------

GET    /search?q=keyword&type=all&limit=20
# Types: all, news, documents, videos, staff, departments

# Response:
# {
#   "results": {
#     "news": [...],
#     "documents": [...],
#     "videos": [...],
#     "staff": [...],
#     "departments": [...]
#   },
#   "total": 150
# }

# -----------------------------------------------------------
# 25. DASHBOARD - Thong ke (admin only)
# -----------------------------------------------------------

GET    /dashboard/stats                    # Thong ke tong quan
GET    /dashboard/news-stats               # Thong ke tin tuc
GET    /dashboard/contact-stats            # Thong ke lien he
GET    /dashboard/salary-stats             # Thong ke luong

# Response: GET /dashboard/stats
# {
#   "totalNews": 500,
#   "publishedNews": 420,
#   "totalStaff": 92,
#   "totalContacts": 150,
#   "pendingContacts": 12,
#   "totalViews": 50000
# }

# -----------------------------------------------------------
# 26. ACTIVITY LOGS - Nhat ky hoat dong
# -----------------------------------------------------------

GET    /activity-logs                      # Nhat ky (admin, phan trang)
GET    /activity-logs/user/{userId}        # Nhat ky theo nguoi dung

# ============================================================
# COMMON RESPONSE FORMATS
# ============================================================

# Success (single object):
# { "id": 1, "name": "...", ... }

# Success (list with pagination):
# {
#   "content": [...],
#   "totalElements": 100,
#   "totalPages": 5,
#   "number": 0,
#   "size": 20
# }

# Error:
# {
#   "status": 404,
#   "error": "Not Found",
#   "message": "Khong tim thay tai khoan voi id=999",
#   "timestamp": "2026-06-16T10:30:00"
# }

# ============================================================
# HTTP STATUS CODES
# ============================================================
# 200 OK            - Thanh cong
# 201 Created       - Tao thanh cong
# 400 Bad Request   - Du lieu dau vao khong hop le
# 401 Unauthorized  - Chua xac thuc
# 403 Forbidden     - Khong co quyen
# 404 Not Found     - Khong tim thay
# 409 Conflict      - Trung lap du lieu
# 500 Internal      - Loi server

# ============================================================
# AUTHENTICATION FLOW
# ============================================================
# 1. POST /auth/login -> token + refreshToken
# 2. Header: Authorization: Bearer <token>
# 3. Token expires in 24h -> POST /auth/refresh
# 4. Logout: POST /auth/logout (optional, invalidate token)