<?php

namespace App\Http\Controllers\Api\Client;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;

class AIChatController extends Controller
{
    public function generate(Request $request)
    {
        $userMessage = $request->input('message');

        // Lấy sản phẩm chưa bị xóa mềm, kèm biến thể chưa bị xóa và thuộc tính
        $products = Product::with(['category', 'variants' => function ($query) {
            $query->whereNull('deleted_at'); // Biến thể chưa xóa mềm
        }, 'variants.attributeValues.attribute'])
            ->whereNull('deleted_at') // Sản phẩm chưa xóa mềm
            ->get(['id', 'ten', 'mo_ta', 'danh_muc_id']);

        // Chuyển dữ liệu sản phẩm thành mô tả văn bản
        $productList = $products->map(function ($product) {
            $variants = $product->variants->map(function ($variant) {
                $attributes = $variant->attributeValues->map(function ($val) {
                    return "{$val->attribute->ten}: {$val->gia_tri}";
                })->implode(', ');

                return "Giá: {$variant->gia}" . ($attributes ? " ({$attributes})" : '');
            })->implode('; ');

            return "ID: {$product->id}, Tên: {$product->ten}, Mô tả: {$product->mo_ta}, Biến thể: {$variants}";
        })->implode("\n");

        // Prompt hệ thống - giới hạn bot chỉ trả lời về sản phẩm
        $messages = [
            [
                'role' => 'system',
                'content' => <<<EOT
Bạn là một trợ lý bán hàng của cửa hàng. Dưới đây là danh sách tất cả sản phẩm hiện có, cùng các biến thể và thuộc tính:

{$productList}

 Bạn chỉ được phép tư vấn, giải thích hoặc trả lời những câu hỏi liên quan đến các sản phẩm trong danh sách này.

 Nếu người dùng hỏi bất kỳ điều gì không liên quan đến sản phẩm (ví dụ: xử lý đơn hàng, logic lập trình, thời tiết, mẹo vặt...), bạn PHẢI trả lời:

"Xin lỗi, tôi chỉ hỗ trợ tư vấn về sản phẩm hiện có trong cửa hàng."

Không được trả lời bất kỳ nội dung nào nằm ngoài danh sách sản phẩm.
EOT
            ],
            [
                'role' => 'user',
                'content' => $userMessage,
            ],
        ];

        // Gửi yêu cầu tới OpenRouter API
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('OPENROUTER_API_KEY'),
            'X-Title' => 'Laravel Chatbot',
        ])->post(env('OPENROUTER_API_URL'), [
            'model' => env('OPENROUTER_MODEL'),
            'messages' => $messages,
        ]);

        // Trả về kết quả hoặc lỗi
        if ($response->successful()) {
            return response()->json($response->json());
        }

        return response()->json([
            'error' => 'Gọi API thất bại',
            'detail' => $response->json(),
        ], $response->status());
    }
}
