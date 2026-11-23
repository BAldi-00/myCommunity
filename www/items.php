<?php
class Item
{
    public ?int $idItem = null;
    public string $name = '';
    public string $category = '';
    public float $value = 0.0;
    public float $weight = 0.0;
    public bool $is_on_blackbuy = false;
    public ?int $user_id = null;
    public ?string $author_name = null;

    public static function fromStdClass(object $row): self
    {
        $item = new self();
        $item->idItem = isset($row->idItem) ? (int)$row->idItem : null;
        $item->name = (string)($row->name ?? '');
        $item->category = (string)($row->category ?? '');
        $item->value = (float)($row->value ?? 0.0);
        $item->weight = (float)($row->weight ?? 0.0);
        $item->is_on_blackbuy = !empty($row->is_on_blackbuy);
        $item->user_id = isset($row->user_id) ? (int)$row->user_id : null;
        $item->author_name = isset($row->author_name) ? (string)$row->author_name : null;
        return $item;
    }
}