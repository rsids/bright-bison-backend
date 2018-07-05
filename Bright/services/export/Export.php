<?php
namespace Bright\services\export;

class Export
{

    public function getTemplates()
    {
        $templates = new \Template();
        $defs = $templates->getTemplateDefinitions();
        return json_encode($defs);
    }
}