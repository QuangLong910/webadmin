<?php

// Chỉ dịch các quy tắc web quản trị đang dùng; quy tắc khác rơi về tiếng Anh (fallback_locale).
return [
    'accepted' => 'Phải tích xác nhận :attribute.',
    'array' => ':attribute không hợp lệ.',
    'boolean' => ':attribute không hợp lệ.',
    'in' => ':attribute không hợp lệ.',
    'integer' => ':attribute phải là số nguyên.',
    'max' => [
        'numeric' => ':attribute không được lớn hơn :max.',
        'string' => ':attribute không được dài quá :max ký tự.',
    ],
    'min' => [
        'numeric' => ':attribute phải từ :min trở lên.',
        'string' => ':attribute phải có ít nhất :min ký tự.',
    ],
    'required' => 'Vui lòng nhập :attribute.',
    'string' => ':attribute phải là chuỗi ký tự.',

    'attributes' => [
        'phone' => 'số điện thoại',
        'password' => 'mật khẩu',
        'approved' => 'kết quả duyệt',
        'reason_note' => 'ghi chú lý do',
        'received_amount' => 'số tiền thực nhận',
        'bank_note' => 'nội dung sao kê',
        'transfer_ref' => 'mã giao dịch',
        'mode' => 'cách tính phí',
    ],
];
