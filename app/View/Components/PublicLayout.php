<?php

namespace App\View\Components;

use App\Models\HomeCategory;
use App\Models\HomeMenu;
use App\Models\OutletType;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class PublicLayout extends Component
{
    public $homeMenus;
    public $searchOutletTypes;
    public $homeCategories;

    public function __construct(
        public ?string $title = null,
        public ?string $description = null,
        public ?string $ogImage = null,
    ) {
        $this->homeMenus = HomeMenu::whereNull('parent_id')
            ->where('is_active', true)
            ->with(['children' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order')->orderBy('label')])
            ->orderBy('sort_order')
            ->orderBy('label')
            ->get();

        $this->searchOutletTypes = OutletType::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $this->homeCategories = HomeCategory::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    public function render(): View
    {
        return view('components.public-layout');
    }
}
