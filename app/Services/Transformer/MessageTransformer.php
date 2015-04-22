<?php namespace App\Services\Transformer;

class MessageTransformer extends Transformer
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
            'messages' => $this->transformMessageCollection($item['messages'])
        ];
    }

    /**
     * Transforms a message collection
     *
     * @param $collection
     * @return array
     */
    public function transformMessageCollection($collection)
    {
        return array_map(array($this, 'transformMessage'), $collection);
    }

    /**
     * Transforms a given message
     *
     * @param array $item
     * @return array
     */
    public function transformMessage(array $item)
    {
        return [
            'id' => (int)$item['id'],
            'user_id' => $item['user_id'],
            'user_name' => $item['user']['first_name'] . ' ' . $item['user']['last_name'],
            'body' => $item['body'],
            'created_at' => $item['created_at'],
            'updated_at' => $item['updated_at']
        ];
    }
}