<?php

namespace Database\Seeders;

use App\Models\ChatbotFaq;
use Illuminate\Database\Seeder;

class ChatbotFaqSeeder extends Seeder
{
    public function run(): void
    {
        $faqs = [
            [
                'category' => 'crypto_basic',
                'question' => 'Bitcoin là gì?',
                'answer' => 'Bitcoin là một loại tiền điện tử phi tập trung, hoạt động trên công nghệ blockchain. Bitcoin không do ngân hàng trung ương phát hành và có nguồn cung giới hạn. Tuy nhiên, giá Bitcoin biến động mạnh nên người dùng cần tìm hiểu kỹ rủi ro trước khi đầu tư.',
                'keywords' => ['bitcoin', 'btc', 'tiền điện tử', 'crypto', 'blockchain'],
            ],
            [
                'category' => 'crypto_basic',
                'question' => 'Ethereum là gì?',
                'answer' => 'Ethereum là một nền tảng blockchain cho phép xây dựng smart contract và ứng dụng phi tập trung. Đồng tiền gốc của Ethereum là ETH. Ethereum thường được dùng trong DeFi, NFT, GameFi và nhiều ứng dụng Web3.',
                'keywords' => ['ethereum', 'eth', 'smart contract', 'web3', 'blockchain'],
            ],
            [
                'category' => 'crypto_basic',
                'question' => 'Stablecoin là gì?',
                'answer' => 'Stablecoin là tiền điện tử được thiết kế để giữ giá ổn định, thường neo theo USD. Ví dụ phổ biến gồm USDT, USDC hoặc DAI. Dù ổn định hơn nhiều coin khác, stablecoin vẫn có rủi ro về tổ chức phát hành, thanh khoản và pháp lý.',
                'keywords' => ['stablecoin', 'usdt', 'usdc', 'dai', 'usd'],
            ],
            [
                'category' => 'crypto_basic',
                'question' => 'DeFi là gì?',
                'answer' => 'DeFi là viết tắt của Decentralized Finance, tức tài chính phi tập trung. DeFi cho phép người dùng vay, cho vay, giao dịch hoặc cung cấp thanh khoản qua smart contract. Rủi ro chính gồm lỗi hợp đồng thông minh, thanh khoản thấp và biến động thị trường.',
                'keywords' => ['defi', 'decentralized finance', 'lending', 'dex', 'yield'],
            ],
            [
                'category' => 'crypto_basic',
                'question' => 'Blockchain là gì?',
                'answer' => 'Blockchain là công nghệ lưu trữ dữ liệu theo chuỗi khối, trong đó các giao dịch được ghi lại công khai, khó sửa đổi và được xác thực bởi mạng lưới. Blockchain là nền tảng của nhiều loại tiền điện tử như Bitcoin và Ethereum.',
                'keywords' => ['blockchain', 'chuỗi khối', 'giao dịch', 'phi tập trung'],
            ],
            [
                'category' => 'crypto_basic',
                'question' => 'Altcoin là gì?',
                'answer' => 'Altcoin là các đồng tiền điện tử khác ngoài Bitcoin. Một số altcoin có mục tiêu công nghệ rõ ràng, nhưng cũng có nhiều dự án rủi ro cao. Khi tìm hiểu altcoin, nên xem tokenomics, đội ngũ, sản phẩm, thanh khoản và cộng đồng.',
                'keywords' => ['altcoin', 'coin khác bitcoin', 'token', 'tokenomics'],
            ],
            [
                'category' => 'risk',
                'question' => 'Rủi ro khi đầu tư crypto là gì?',
                'answer' => 'Crypto có nhiều rủi ro như biến động giá mạnh, mất private key, scam, sàn bị tấn công, dự án thiếu minh bạch, thanh khoản thấp và thay đổi pháp lý. Người dùng không nên đầu tư nhiều hơn số tiền có thể chấp nhận mất.',
                'keywords' => ['rủi ro', 'risk', 'đầu tư', 'mất tiền', 'biến động', 'scam'],
            ],
            [
                'category' => 'risk',
                'question' => 'Có nên chia sẻ private key hoặc seed phrase không?',
                'answer' => 'Không. Bạn tuyệt đối không nên chia sẻ private key hoặc seed phrase cho bất kỳ ai. Nếu người khác có private key hoặc seed phrase, họ có thể kiểm soát toàn bộ tài sản trong ví của bạn.',
                'keywords' => ['private key', 'seed phrase', 'mật khẩu ví', 'ví crypto', 'bảo mật'],
            ],
            [
                'category' => 'risk',
                'question' => 'Làm sao nhận biết scam crypto?',
                'answer' => 'Một số dấu hiệu scam gồm: cam kết lợi nhuận chắc chắn, yêu cầu gửi tiền trước, yêu cầu seed phrase/private key, giả mạo admin, dự án không minh bạch hoặc thúc ép mua nhanh. Hãy kiểm tra nguồn thông tin và không gửi tài sản khi chưa hiểu rõ.',
                'keywords' => ['scam', 'lừa đảo', 'cam kết lợi nhuận', 'airdrop', 'telegram', 'private key'],
            ],
            [
                'category' => 'risk',
                'question' => 'Có nên tin cam kết lợi nhuận trong crypto không?',
                'answer' => 'Không nên tin tuyệt đối vào các cam kết lợi nhuận chắc chắn. Crypto là thị trường biến động mạnh, không ai có thể đảm bảo lợi nhuận cố định mà không có rủi ro. Những lời hứa lợi nhuận cao, không rủi ro thường là dấu hiệu cần cảnh giác.',
                'keywords' => ['lợi nhuận', 'cam kết lợi nhuận', 'profit', 'guaranteed', 'không rủi ro'],
            ],
            [
                'category' => 'website',
                'question' => 'Làm sao để xem giá crypto?',
                'answer' => 'Bạn có thể vào mục “Giá Crypto” trên thanh điều hướng để xem bảng giá các đồng tiền điện tử. Dữ liệu giá có thể được cập nhật bằng command lấy dữ liệu từ API bên ngoài.',
                'keywords' => ['giá crypto', 'xem giá', 'coin', 'crypto price', 'bảng giá'],
            ],
            [
                'category' => 'website',
                'question' => 'Làm sao để đọc blog?',
                'answer' => 'Bạn có thể vào mục “Blog” để xem các bài viết đã được admin kiểm duyệt. Bạn cũng có thể tìm kiếm bài viết hoặc lọc theo chuyên mục.',
                'keywords' => ['blog', 'đọc bài', 'bài viết', 'xem bài', 'category', 'chuyên mục'],
            ],
            [
                'category' => 'website',
                'question' => 'Làm sao để xem tin tức?',
                'answer' => 'Bạn có thể vào mục “Tin tức” để xem các tin tài chính và crypto. Tin tức có thể được phân loại theo chuyên mục, nguồn tin và thời gian xuất bản.',
                'keywords' => ['tin tức', 'news', 'xem tin', 'source', 'published'],
            ],
            [
                'category' => 'website',
                'question' => 'Làm sao để tìm kiếm bài viết hoặc tin tức?',
                'answer' => 'Bạn có thể dùng thanh tìm kiếm ở trang Blog hoặc Tin tức. Hệ thống hỗ trợ tìm theo tiêu đề, nội dung và chuyên mục để giúp người dùng tìm nội dung liên quan nhanh hơn.',
                'keywords' => ['tìm kiếm', 'search', 'lọc', 'filter', 'category', 'chuyên mục'],
            ],
            [
                'category' => 'website',
                'question' => 'Làm sao để bình luận?',
                'answer' => 'Bạn cần đăng nhập tài khoản trước. Sau đó vào chi tiết một bài blog hoặc tin tức, kéo xuống phần bình luận và nhập nội dung. Bạn có thể xóa bình luận của chính mình sau khi đăng.',
                'keywords' => ['bình luận', 'comment', 'xóa comment', 'đăng nhập'],
            ],
            [
                'category' => 'author',
                'question' => 'Làm sao để trở thành tác giả?',
                'answer' => 'Sau khi đăng ký tài khoản, bạn có thể vào mục “Đăng ký làm tác giả”, hoàn thiện hồ sơ chuyên môn, kinh nghiệm crypto/tài chính và gửi đơn. Admin sẽ xem xét hồ sơ trước khi cấp quyền AUTHOR.',
                'keywords' => ['tác giả', 'author', 'đăng ký tác giả', 'xin làm author', 'viết bài'],
            ],
            [
                'category' => 'author',
                'question' => 'Vì sao tôi chưa đăng được bài?',
                'answer' => 'Chỉ tài khoản có quyền AUTHOR mới được đăng bài. Nếu bạn mới là USER, bạn cần gửi đơn đăng ký làm tác giả và chờ admin phê duyệt.',
                'keywords' => ['không đăng được bài', 'không viết được', 'author', 'role', 'quyền'],
            ],
            [
                'category' => 'author',
                'question' => 'Vì sao bài viết của tôi chưa hiển thị?',
                'answer' => 'Bài viết của AUTHOR sau khi gửi sẽ ở trạng thái chờ duyệt. Chỉ khi admin phê duyệt, bài viết mới được hiển thị công khai trên trang Blog.',
                'keywords' => ['bài chưa hiện', 'chưa hiển thị', 'pending', 'chờ duyệt', 'duyệt bài'],
            ],
            [
                'category' => 'author',
                'question' => 'Nếu bài viết bị từ chối thì làm gì?',
                'answer' => 'Nếu bài viết bị từ chối, bạn có thể xem lý do từ chối trong mục “Bài của tôi”, sau đó chỉnh sửa nội dung và gửi duyệt lại. Bài sẽ quay về trạng thái chờ admin kiểm duyệt.',
                'keywords' => ['bị từ chối', 'reject', 'sửa bài', 'gửi lại', 'lý do từ chối'],
            ],
            [
                'category' => 'admin',
                'question' => 'Admin có thể làm gì?',
                'answer' => 'Admin có thể duyệt đơn đăng ký tác giả, duyệt hoặc từ chối bài blog, quản lý tin tức, quản lý bình luận và xem dashboard tổng quan của hệ thống.',
                'keywords' => ['admin', 'quản trị', 'dashboard', 'duyệt', 'quản lý'],
            ],
            [
                'category' => 'admin',
                'question' => 'Dashboard dùng để làm gì?',
                'answer' => 'Dashboard giúp admin xem nhanh tình trạng hệ thống như tổng người dùng, tác giả, đơn chờ duyệt, bài blog chờ duyệt, tin tức, bình luận, chuyên mục và dữ liệu crypto.',
                'keywords' => ['dashboard', 'tổng quan', 'thống kê', 'admin'],
            ],
            [
                'category' => 'admin',
                'question' => 'Admin duyệt bài như thế nào?',
                'answer' => 'Admin vào mục “Phê duyệt blog”, xem nội dung bài viết đang chờ duyệt, sau đó có thể duyệt để công khai hoặc từ chối kèm lý do. Nếu bị từ chối, author có thể sửa và gửi lại.',
                'keywords' => ['duyệt bài', 'approve', 'reject', 'phê duyệt blog', 'admin blog'],
            ],
            [
                'category' => 'disclaimer',
                'question' => 'Thông tin trên website có phải lời khuyên đầu tư không?',
                'answer' => 'Không. Nội dung trên website chỉ mang tính tham khảo và hỗ trợ học tập. Đây không phải lời khuyên đầu tư. Người dùng cần tự nghiên cứu và chịu trách nhiệm với quyết định tài chính của mình.',
                'keywords' => ['lời khuyên đầu tư', 'khuyến nghị', 'disclaimer', 'tư vấn', 'đầu tư'],
            ],
        ];

        foreach ($faqs as $faq) {
            ChatbotFaq::updateOrCreate(
                ['question' => $faq['question']],
                [
                    'answer' => $faq['answer'],
                    'keywords' => $faq['keywords'],
                    'category' => $faq['category'],
                    'is_active' => true,
                ]
            );
        }
    }
}