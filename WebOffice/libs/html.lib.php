<?php
namespace WebOffice;

class HTML {
    private $elements = []; // Store all elements
    private $stack = [];    // Stack to manage current context
    private $current = null; // Current element being operated on
    private $doctype = '';   // Store doctype based on version

    /**
     * Set the HTML version
     * @param int $version 5 for HTML5 or 4 for HTML4
     */
    public function __construct(int $version=5) {
        if ($version === 5) {
            $this->doctype = '<!DOCTYPE html>';
        } elseif ($version === 4) {
            $this->doctype = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">';
        } else {
            // Default to HTML5 doctype if an unknown version is provided
            $this->doctype = '<!DOCTYPE html>';
        }
    }

    // Helper method to create a new element array
    private function createElement($tag, $class = '', $id = '', $attr = []): array {
        return [
            'tag' => $tag,
            'attributes' => array_merge(
                $attr,
                array_filter([
                    'class' => $class,
                    'id' => $id,
                ])
            ),
            'children' => [],
            'content' => '',
        ];
    }

    /**
     * Add a root element
     * @param string $tag Tagname
     * @return HTML
     */
    public function addElement(string $tag): static {
        $element = $this->createElement($tag);
        $this->elements[] = &$element;
        $this->current = &$element;
        $this->stack = [&$element]; // Reset stack to just this root
        return $this;
    }

    /**
     * Add a child element to the current element
     * @param string $tag Element tagname
     * @return HTML
     */
    public function addChild(string $tag): static {
        $child = $this->createElement($tag);
        if ($this->current !== null) {
            $this->current['children'][] = &$child;
            $this->stack[] = &$child; // Push current to stack
            $this->current = &$child;
        }
        return $this;
    }

    /**
     * Move up to the parent element
     * @return HTML
     */
    public function moveUp(): static {
        if (count($this->stack) > 1) {
            array_pop($this->stack);
            $this->current = &$this->stack[count($this->stack) - 1];
        }
        return $this;
    }

    /**
     * Move to the last child of the current element
     * @return HTML
     */
    public function moveDown(): static {
        if (!empty($this->current['children'])) {
            $lastChild =& $this->current['children'][count($this->current['children']) - 1];
            $this->stack[] = &$lastChild;
            $this->current = &$lastChild;
        }
        return $this;
    }

    /**
     * Sets the content of the element
     * @param string $content Content
     * @return HTML
     */
    public function setContent(string $content): static {
        if ($this->current !== null) {
            $this->current['content'] = $content;
        }
        return $this;
    }

    /**
     * Sets classname to element
     * @param string $class Element classname
     * @return HTML
     */
    public function setClass(string $class=''): static {
        if ($this->current !== null) {
            $this->current['attributes']['class'] = $class;
        }
        return $this;
    }

    /**
     * Sets the ID
     * @param string $id Element ID
     * @return HTML
     */
    public function setID(string $id=''): static {
        if ($this->current !== null) {
            $this->current['attributes']['id'] = $id;
        }
        return $this;
    }

    /**
     * Sets attributes
     * @param array $attr Element attributes
     * @return HTML
     */
    public function setAttr(array $attr=[]): static{
        if ($this->current !== null) {
            $this->current['attributes'] = array_merge($this->current['attributes'],$attr);
        }
        return $this;
    }

    // Render the entire structure as HTML including doctype
    public function render(): string {
        $html = "$this->doctype\n"; // Include doctype at the top
        foreach ($this->elements as &$el) {
            $html .= $this->renderElement($el);
        }
        return $html;
    }

    // Helper function to render a single element
    private function renderElement($element): string {
        $attributes = '';
        foreach ($element['attributes'] as $key => $value) {
            $attributes .= " {$key}=\"" . htmlspecialchars($value) . "\"";
        }
        $html = "<{$element['tag']}{$attributes}>";
        if (!empty($element['content'])) {
            $html .= htmlspecialchars($element['content']);
        }
        foreach ($element['children'] as &$child) {
            $html .= $this->renderElement($child);
        }
        $html .= "</{$element['tag']}>";
        return $html;
    }
}