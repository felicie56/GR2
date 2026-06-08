<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Tài chính cá nhân',
                'description' => 'Kiến thức quản lý tiền bạc, tiết kiệm, đầu tư và quản trị tài chính cá nhân.',
            ],
            [
                'name' => 'Bitcoin',
                'description' => 'Tin tức, phân tích và kiến thức liên quan đến Bitcoin.',
            ],
            [
                'name' => 'Ethereum',
                'description' => 'Tin tức, phân tích và kiến thức liên quan đến Ethereum.',
            ],
            [
                'name' => 'Altcoin',
                'description' => 'Thông tin về các đồng tiền điện tử ngoài Bitcoin và Ethereum.',
            ],
            [
                'name' => 'DeFi',
                'description' => 'Tài chính phi tập trung, lending, DEX, yield và các giao thức DeFi.',
            ],
            [
                'name' => 'Stablecoin',
                'description' => 'Thông tin về stablecoin, thanh khoản và rủi ro liên quan.',
            ],
            [
                'name' => 'Market News',
                'description' => 'Tin tức thị trường tài chính và crypto.',
            ],
            [
                'name' => 'Risk Management',
                'description' => 'Quản trị rủi ro, bảo mật và phòng tránh lừa đảo trong crypto.',
            ],
            [
                'name' => 'Kiến thức cơ bản',
                'description' => 'Nội dung dành cho người mới bắt đầu tìm hiểu tài chính và crypto.',
            ],
            [
                'name' => 'Phân tích thị trường',
                'description' => 'Các bài viết phân tích xu hướng, dữ liệu và biến động thị trường.',
            ],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['slug' => Str::slug($category['name'])],
                [
                    'name' => $category['name'],
                    'description' => $category['description'],
                    'is_active' => true,
                ]
            );
        }
    }
}