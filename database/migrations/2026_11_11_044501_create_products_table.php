<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
       /**
     * Run the migrations.
     */
    public function up(): void
    {
        // products table ကို စတင်ဆောက်လုပ်ခြင်း
        Schema::create('products', function (Blueprint $table) {
            // Product ရဲ့ auto-increment primary key ID
            $table->id();

            // categories table ရဲ့ id နှင့် ချိတ်ဆက်ထားသော foreign key (1:N relationship)
            // category ဖျက်ပါက product မပျက်စီးစေရန် restrictOnDelete() ဖြင့် ကာကွယ်ထားသည်
            $table->foreignId('category_id')->constrained('categories')->restrictOnDelete();

            // ကုန်ပစ္စည်း အမည် (ဥပမာ- Coca Cola 330ml)
            $table->string('name');

            // ရောင်းဈေးနှုန်း (ဒသမ ၂ နေရာအထိ တိကျစွာသိမ်းဆည်းသော decimal)
            $table->decimal('price', 10, 2);

            // လက်ကျန်အရေအတွက် (အနှုတ်မဖြစ်နိုင်သော အပေါင်းကိန်းပြည့် integer)
            $table->unsignedInteger('stock_quantity')->default(0);

            // စာရင်းစသွင်းချိန်နှင့် ပြင်ဆင်ချိန် (created_at, updated_at)
            $table->timestamps();

            // စာရင်းမှ ဖျက်လိုက်သော်လည်း စာရင်းဟောင်းမပျက်စေရန် အချိန်မှတ်သားပေးသည့် Soft Delete (deleted_at)
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // rollback ပြုလုပ်ပါက products table ကို ပြန်ဖျက်ပေးခြင်း
        Schema::dropIfExists('products');
    }

};
