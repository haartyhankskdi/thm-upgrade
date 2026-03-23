## Usage Examples for top links toolbar
**Default block usage with static link**

hyva_default.xml
```xml
<referenceBlock name="am-hyva-top-links-container">
    <block template="Amasty_Hyva::link-default.phtml" before="-">
        <arguments>
            <argument name="link_href" xsi:type="string"><![CDATA[/link-text]]></argument>
            <argument name="link_text" xsi:type="string">Link Text</argument>
        </arguments>
    </block>
</referenceBlock>
```

**Custom link implementation**

hyva_default.xml
```xml
<referenceBlock name="am-hyva-top-links-container">
    <block template="Amasty_FaqHyva::top.phtml" before="-">
        <arguments>
            <argument name="view_model" xsi:type="object">Amasty\FaqHyva\ViewModel\Link</argument>
        </arguments>
    </block>
</referenceBlock>
```
Amasty_FaqHyva::top.phtml
```html
<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 */
declare(strict_types=1);

use Magento\Framework\Escaper;
use Magento\Framework\View\Element\Template;

/** @var $block Template */
/** @var $escaper Escaper */
/** @var $viewModel \Amasty\FaqHyva\ViewModel\Link */

$viewModel = $block->getViewModel();
?>
<?php if ($viewModel->isTopLinkEnabled()): ?>
    <a href="<?= $escaper->escapeUrl($viewModel->getLink()) ?>" class="hover:underline">
        <?= $escaper->escapeHtml($viewModel->getText()) ?>
    </a>
<?php endif; ?>
```
