<?php
/**
 * Created by IntelliJ IDEA.
 * User: nikit
 * Date: 24.06.2019
 * Time: 14:11
 */

namespace esas\cmsgate\hutkigrosh\view\client;

use esas\cmsgate\utils\htmlbuilder\Attributes as attribute;
use esas\cmsgate\utils\htmlbuilder\Elements as element;

class CompletionPanelHutkigroshVirtuemart extends CompletionPanelHutkigrosh
{
    public function getCssClass4Button()
    {
        return "product-details";
    }

    public function elementTabHeader($key, $header)
    {
        return element::div(
            attribute::clazz("tab-header " . $this->getCssClass4TabHeader()),
            attribute::style("display:inline-block"),
            element::div(
                attribute::clazz($this->getCssClass4TabHeaderLabel()),
                attribute::style("text-align:left; font-weight:bold"),
                element::content($header)
            )
        );
    }

}