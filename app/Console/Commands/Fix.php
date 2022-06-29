<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;

class Fix extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:product';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $data = Product::query()->where('category_id', null)->get();
        foreach ($data as $item) {
            $category = \App\Models\Category::query()->where('is_directory', false)->inRandomOrder()->first();
            Product::query()->where('id', $item->id)->update(
                [
                    'category_id'  => $category ? $category->id : null,
                ]
            );
            echo 'init product_id:' . $item->id . PHP_EOL;
        }
        return 0;
    }
}
