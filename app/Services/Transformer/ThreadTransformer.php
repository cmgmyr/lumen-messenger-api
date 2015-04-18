<?php namespace App\Services\Transformer;

class ThreadTransformer extends Transformer
{

    /**
     * Transforms a given array
     *
     * @param array $item
     * @return array
     */
    public function transform(array $item)
    {
        return [
            'id' => (int)$item['id'],
            'subject' => $item['subject'],
            'created_at' => $item['created_at'],
            'updated_at' => $item['updated_at'],
            'is_unread' => (bool)$item['is_unread'],
        ];
    }
}