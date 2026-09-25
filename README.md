# Web quản trị — Sàn Thuê Xe Tự Lái

Trang cho người vận hành (vai `VAN_HANH`): duyệt giấy tờ → duyệt xe → xác nhận tiền vào → quyết toán → chi trả → đối soát sổ cái.

Laravel chỉ là client: mọi dữ liệu lấy từ backend qua `app/Services/ThueXeApi.php`, không đọc DB trực tiếp.

## Chạy

1. Bật **Tailscale** (backend nằm ở máy trong mạng Tailscale của nhóm).
2. Trong `.env`, trỏ tới backend:
   ```
   API_BASE_URL=http://100.86.242.10:5160
   ```
3. Chạy (dùng Terminal của Laragon, hoặc thêm PHP của Laragon vào PATH):
   ```
   php artisan serve
   ```
4. Mở http://127.0.0.1:8000, đăng nhập bằng tài khoản có vai `VAN_HANH`.

## Màn hình

| Menu | Backend |
|---|---|
| Duyệt giấy tờ | `GET /admin/documents`, `POST /admin/documents/{id}/review` |
| Duyệt xe | `GET /admin/cars`, `POST /admin/cars/{id}/review` |
| Đơn thuê & tiền vào | `GET /owner/bookings` (tạm), `GET /bookings/{id}`, `POST /admin/payments/{id}/confirm` |
| Quyết toán (trong trang đơn) | `POST /admin/bookings/{id}/settle` |
| Chi trả | `GET /admin/payouts`, `POST /admin/payouts/{id}/paid` |
| Sổ cái | `GET /admin/ledger?bookingId=` |

## Còn chờ backend

- `GET /admin/bookings` — hiện mượn `/owner/bookings` nên chỉ thấy đơn của xe thuộc tài khoản đang đăng nhập.
- Số tài khoản của khách để hoàn cọc (lệnh chi đang trả `CHUA_CO`).
- Xác thực hai yếu tố cho web quản trị (đặc tả §9).
