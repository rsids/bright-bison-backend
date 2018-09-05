<?php
namespace Bright\services\export;

class Export
{

    public function getTemplates()
    {
        $templates = new \Template();
        $defs = $templates->getTemplateDefinitions();
        return ['templates' => $defs];
    }

    public function getChildren()
    {
        return ['children' => $this->_exportChildren(1)];
    }

    private function _exportChildren($parentId, $level = 1)
    {

        $tree = new \Tree();
        $nav = $tree->getChildren($parentId);
        foreach ($nav as &$child) {
            if ($child->numChildren > 0) {
                $child->children = $this->_exportChildren($child->treeId, $level + 1);
            }
        }

        return $nav;
    }
}