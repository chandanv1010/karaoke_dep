<?php
namespace App\Classes;

class Introduce{

    public function config(){
        $data['block_1'] = [
            'label' => 'Khối 1',
            'description' => 'Cài đặt đầy đủ thông tin khối dưới đây',
            'value' => [
                'company' =>  ['type' => 'text', 'label' => 'Tên đơn vị'],
                'description' =>  ['type' => 'textarea', 'label' => 'Mô tả ngắn'],
                'title' => ['type' => 'text', 'label' => 'Tên tiếng trung'],
                'tax' => ['type' => 'text', 'label' => 'Mã số thuế'],
                'year' => ['type' => 'text', 'label' => 'Năm hoạt động'],
                'rank' => ['type' => 'text', 'label' => 'Xếp hạng dịch vụ'],
                'en' => ['type' => 'text', 'label' => 'Tên tiếng anh'],
                'email' => ['type' => 'text', 'label' => 'Email'],
                'hotline' => ['type' => 'text', 'label' => 'Hotline'],
                'connect_count' => ['type' => 'text', 'label' => 'Số trường kết nối'],
                'image' => ['type' => 'images', 'label' => 'Ảnh'],
                'fanpage_link' => ['type' => 'text', 'label' => 'Fanpage Link'],
                'button_label' => ['type' => 'text', 'label' => 'Nhãn nút'],
                'button_link' => ['type' => 'text', 'label' => 'Link nút'],
            ]
        ];
        $data['block_2'] = [
            'label' => 'Khối 2',
            'description' => 'Cài đặt đầy đủ thông tin khối dưới đây',
            'value' => [
                'content' =>  ['type' => 'editor', 'label' => 'Giới thiệu'],
                'box_1_number' => ['type' => 'text', 'label' => 'Số 1'],
                'box_1_text' => ['type' => 'text', 'label' => 'Text 1'],
                'box_2_number' => ['type' => 'text', 'label' => 'Số 2'],
                'box_2_text' => ['type' => 'text', 'label' => 'Text 2'],
                'box_3_number' => ['type' => 'text', 'label' => 'Số 3'],
                'box_3_text' => ['type' => 'text', 'label' => 'Text 3'],
                'box_4_number' => ['type' => 'text', 'label' => 'Số 4'],
                'box_4_text' => ['type' => 'text', 'label' => 'Text 4'],
                'box_5_number' => ['type' => 'text', 'label' => 'Số 5'],
                'box_5_text' => ['type' => 'text', 'label' => 'Text 5'],
                'box_6_number' => ['type' => 'text', 'label' => 'Số 6'],
                'box_6_text' => ['type' => 'text', 'label' => 'Text 6'],
            ]
        ];
        $data['block_3'] = [
            'label' => 'Khối 3',
            'description' => 'Cài đặt đầy đủ thông tin khối dưới đây',
            'value' => [
                'heading' =>  ['type' => 'text', 'label' => 'Tiêu đề'],
                'description' =>  ['type' => 'textarea', 'label' => 'mô tả'],
                'image_1' => ['type' => 'images', 'label' => 'Ảnh 1'],
                'image_2' => ['type' => 'images', 'label' => 'Ảnh 2'],
                'image_3' => ['type' => 'images', 'label' => 'Ảnh 3'],
               
            ]
        ];
        $data['block_4'] = [
            'label' => 'Khối 4',
            'description' => 'Cài đặt đầy đủ thông tin khối dưới đây',
            'value' => [
                'heading' =>  ['type' => 'text', 'label' => 'Tiêu đề'],
                'description' =>  ['type' => 'textarea', 'label' => 'mô tả'],
                'image' => ['type' => 'images', 'label' => 'Ảnh'],
                'video' => ['type' => 'textarea', 'label' => 'Video'],
               
               
            ]
        ];
        $data['block_5'] = [
            'label' => 'Khối 5 Tại sao lựa chọn chúng tôi',
            'description' => 'Cài đặt đầy đủ thông tin khối dưới đây',
            'value' => [
                'small_heading' =>  ['type' => 'text', 'label' => 'Tiêu đề bé'],
                'heading' =>  ['type' => 'text', 'label' => 'Tiêu đề'],
                'description' =>  ['type' => 'editor', 'label' => 'mô tả'],
                'image' => ['type' => 'images', 'label' => 'Ảnh'],
            ]
        ];
        $data['block_6'] = [
            'label' => 'Khối 6 Tại sao lựa chọn chúng tôi',
            'description' => 'Cài đặt đầy đủ thông tin khối dưới đây',
            'value' => [
                'image' => ['type' => 'images', 'label' => 'Ảnh'],
                'heading' =>  ['type' => 'text', 'label' => 'Tiêu đề'],
                'description' =>  ['type' => 'editor', 'label' => 'mô tả'],
                'block_1_title' => ['type' => 'text', 'label' => 'Tiêu đề 1'],
                'block_1_description' => ['type' => 'textarea', 'label' => 'Mô tả 1'],
                'block_2_title' => ['type' => 'text', 'label' => 'Tiêu đề 2'],
                'block_2_description' => ['type' => 'textarea', 'label' => 'Mô tả 2'],
                'block_3_title' => ['type' => 'text', 'label' => 'Tiêu đề 3'],
                'block_3_description' => ['type' => 'textarea', 'label' => 'Mô tả 3'],
            ]
        ];
        $data['block_7'] = [
            'label' => 'Khối 7 Giải thưởng nhận được',
            'description' => 'Cài đặt đầy đủ thông tin khối dưới đây',
            'value' => [
                'image' => ['type' => 'images', 'label' => 'Ảnh'],
                'heading' =>  ['type' => 'text', 'label' => 'Tiêu đề'],
                'description' =>  ['type' => 'editor', 'label' => 'mô tả'],
                'block_1_title' => ['type' => 'text', 'label' => 'Tiêu đề 1'],
                'block_1_description' => ['type' => 'textarea', 'label' => 'Mô tả 1'],
                'block_2_title' => ['type' => 'text', 'label' => 'Tiêu đề 2'],
                'block_2_description' => ['type' => 'textarea', 'label' => 'Mô tả 2'],
            ]
        ];
        $data['block_8'] = [
            'label' => 'Khối 8 Các dịch vụ',
            'description' => 'Cài đặt đầy đủ thông tin khối dưới đây',
            'value' => [
                'heading' =>  ['type' => 'text', 'label' => 'Tiêu đề'],
                'description' =>  ['type' => 'textarea', 'label' => 'mô tả'],
                'image' => ['type' => 'images', 'label' => 'Ảnh nền'],
                'block_1_title' => ['type' => 'text', 'label' => 'Tiêu đề 1'],
                'block_1_description' => ['type' => 'editor', 'label' => 'Mô tả 1'],
                'block_2_title' => ['type' => 'text', 'label' => 'Tiêu đề 2'],
                'block_2_description' => ['type' => 'textarea', 'label' => 'Mô tả 2'],
                'block_3_title' => ['type' => 'text', 'label' => 'Tiêu đề 3'],
                'block_3_description' => ['type' => 'textarea', 'label' => 'Mô tả 3'],
                'block_4_title' => ['type' => 'text', 'label' => 'Tiêu đề 4'],
                'block_4_description' => ['type' => 'textarea', 'label' => 'Mô tả 4'],
                'block_5_title' => ['type' => 'text', 'label' => 'Tiêu đề 5'],
                'block_5_description' => ['type' => 'textarea', 'label' => 'Mô tả 5'],
            ]
        ];
         $data['block_9'] = [
            'label' => 'Khối 9 Lý do lựa chọn',
            'description' => 'Cài đặt đầy đủ thông tin khối dưới đây',
            'value' => [
                'heading' =>  ['type' => 'text', 'label' => 'Tiêu đề'],
                'description' =>  ['type' => 'textarea', 'label' => 'mô tả'],
                'block_1_title' => ['type' => 'text', 'label' => 'Tiêu đề 1'],
                'block_1_description' => ['type' => 'editor', 'label' => 'Mô tả 1'],
                'block_2_title' => ['type' => 'text', 'label' => 'Tiêu đề 2'],
                'block_2_description' => ['type' => 'editor', 'label' => 'Mô tả 2'],
                'block_3_title' => ['type' => 'text', 'label' => 'Tiêu đề 3'],
                'block_3_description' => ['type' => 'editor', 'label' => 'Mô tả 3'],
                'block_4_title' => ['type' => 'text', 'label' => 'Tiêu đề 4'],
                'block_4_description' => ['type' => 'editor', 'label' => 'Mô tả 4'],
            ]
        ];
        return $data;
    }
	
}
