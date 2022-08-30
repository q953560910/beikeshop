<?php
/**
 * Render.php
 *
 * @copyright  2022 opencart.cn - All Rights Reserved
 * @link       http://www.guangdawangluo.com
 * @author     Edward Yang <yangjin@opencart.cn>
 * @created    2022-07-08 17:09:15
 * @modified   2022-07-08 17:09:15
 */

namespace Beike\Admin\View\DesignBuilders;

use Illuminate\View\Component;
use Illuminate\Contracts\View\View;

class SlideShow extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View
     */
    public function render(): View
    {
        $data['register'] = [
            'code' => 'slideshow',
            'sort' => 0,
            'name' => trans('admin/design_builder.module_slideshow'),
            'icon' => '&#xe61b;',
            'style' => 'font-size: 40px;',
        ];

        return view('admin::pages.design.module.slideshow', $data);
    }
}
