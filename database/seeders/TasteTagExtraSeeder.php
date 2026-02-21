<?php

namespace Database\Seeders;

use App\Models\TasteTag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TasteTagExtraSeeder extends Seeder
{
    public function run(): void
    {
        // Additional tags (10 per category)
        $extras = [
            'wood' => [
                ['slug' => 'toasted-oak', 'name' => 'Toasted oak'],
                ['slug' => 'sandalwood', 'name' => 'Sandalwood'],
                ['slug' => 'cedary', 'name' => 'Cedary'],
                ['slug' => 'charred-wood', 'name' => 'Charred wood'],
                ['slug' => 'resin', 'name' => 'Resin'],
                ['slug' => 'bark', 'name' => 'Bark'],
                ['slug' => 'teak', 'name' => 'Teak'],
                ['slug' => 'maple-wood', 'name' => 'Maple wood'],
                ['slug' => 'pine-needle', 'name' => 'Pine needle'],
                ['slug' => 'old-oak', 'name' => 'Old oak'],
            ],
            'sweet' => [
                ['slug' => 'brown-sugar', 'name' => 'Brown sugar'],
                ['slug' => 'molasses', 'name' => 'Molasses'],
                ['slug' => 'maple-syrup', 'name' => 'Maple syrup'],
                ['slug' => 'butterscotch', 'name' => 'Butterscotch'],
                ['slug' => 'custard', 'name' => 'Custard'],
                ['slug' => 'caramelized-fruit', 'name' => 'Caramelized fruit'],
                ['slug' => 'sugar-candy', 'name' => 'Sugar candy'],
                ['slug' => 'brownie', 'name' => 'Brownie'],
                ['slug' => 'sweet-tobacco', 'name' => 'Sweet tobacco'],
                ['slug' => 'milk-chocolate', 'name' => 'Milk chocolate'],
            ],
            'floral' => [
                ['slug' => 'elderflower', 'name' => 'Elderflower'],
                ['slug' => 'jasmine', 'name' => 'Jasmine'],
                ['slug' => 'orange-blossom', 'name' => 'Orange blossom'],
                ['slug' => 'acacia', 'name' => 'Acacia'],
                ['slug' => 'peony', 'name' => 'Peony'],
                ['slug' => 'gardenia', 'name' => 'Gardenia'],
                ['slug' => 'chamomile', 'name' => 'Chamomile'],
                ['slug' => 'lilac', 'name' => 'Lilac'],
                ['slug' => 'marigold', 'name' => 'Marigold'],
                ['slug' => 'orange-peel-flower', 'name' => 'Orange peel flower'],
            ],
            'fruity' => [
                ['slug' => 'banana', 'name' => 'Banana'],
                ['slug' => 'strawberry', 'name' => 'Strawberry'],
                ['slug' => 'blackberry', 'name' => 'Blackberry'],
                ['slug' => 'cherry', 'name' => 'Cherry'],
                ['slug' => 'plum', 'name' => 'Plum'],
                ['slug' => 'apricot', 'name' => 'Apricot'],
                ['slug' => 'mango', 'name' => 'Mango'],
                ['slug' => 'fig', 'name' => 'Fig'],
                ['slug' => 'grape', 'name' => 'Grape'],
                ['slug' => 'orange', 'name' => 'Orange'],
            ],
            'peat' => [
                ['slug' => 'peat-smoke', 'name' => 'Peat smoke'],
                ['slug' => 'tar', 'name' => 'Tar'],
                ['slug' => 'charcoal', 'name' => 'Charcoal'],
                ['slug' => 'damp-earth', 'name' => 'Damp earth'],
                ['slug' => 'bonfire', 'name' => 'Bonfire'],
                ['slug' => 'peat-ash', 'name' => 'Peat ash'],
                ['slug' => 'smoked-meat', 'name' => 'Smoked meat'],
                ['slug' => 'sea-smoke', 'name' => 'Sea smoke'],
                ['slug' => 'coal', 'name' => 'Coal'],
                ['slug' => 'tar-ink', 'name' => 'Tar/Ink'],
            ],
            'spice' => [
                ['slug' => 'white-pepper', 'name' => 'White pepper'],
                ['slug' => 'cardamom', 'name' => 'Cardamom'],
                ['slug' => 'anise', 'name' => 'Anise'],
                ['slug' => 'allspice', 'name' => 'Allspice'],
                ['slug' => 'coriander', 'name' => 'Coriander'],
                ['slug' => 'clove-bud', 'name' => 'Clove bud'],
                ['slug' => 'star-anise', 'name' => 'Star anise'],
                ['slug' => 'szechuan-pepper', 'name' => 'Szechuan pepper'],
                ['slug' => 'cayenne', 'name' => 'Cayenne'],
                ['slug' => 'gingerbread', 'name' => 'Gingerbread'],
            ],
            'nuts' => [
                ['slug' => 'pecan', 'name' => 'Pecan'],
                ['slug' => 'macadamia', 'name' => 'Macadamia'],
                ['slug' => 'peanut', 'name' => 'Peanut'],
                ['slug' => 'cashew', 'name' => 'Cashew'],
                ['slug' => 'pistachio', 'name' => 'Pistachio'],
                ['slug' => 'brazil-nut', 'name' => 'Brazil nut'],
                ['slug' => 'walnut-oil', 'name' => 'Walnut oil'],
                ['slug' => 'almond-butter', 'name' => 'Almond butter'],
                ['slug' => 'roasted-nut', 'name' => 'Roasted nut'],
                ['slug' => 'marzipan', 'name' => 'Marzipan'],
            ],
        ];

        $orderStart = 100;
        foreach ($extras as $category => $items) {
            $categoryRow = DB::table('taste_tag_categories')->where('slug', $category)->first();
            if (! $categoryRow) continue;
            foreach ($items as $i => $t) {
                $slug = $t['slug'];
                $name = $t['name'];
                TasteTag::updateOrCreate([
                    'slug' => $slug,
                ], [
                    'name' => $name,
                    'category_id' => $categoryRow->id,
                    'order' => $orderStart + $categoryRow->order + $i,
                ]);
            }
        }
    }
}
