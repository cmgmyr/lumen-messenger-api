<?php

namespace app\Http\Controllers;

use Illuminate\Http\Response;

class ApiController extends Controller
{
    /**
     * @var int
     */
    protected $statusCode = Response::HTTP_OK;

    /**
     * @param $statusCode
     * @return $this
     */
    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * Builds a pagination array.
     *
     * @param $data
     * @return mixed
     */
    public function buildPagination($data)
    {
        $pagination = $data->toArray();
        unset($pagination['data']);

        return $pagination;
    }

    /**
     * @param $data
     * @param array $headers
     * @return mixed
     */
    public function respond($data, $headers = array())
    {
        return response()->json($data, $this->getStatusCode(), $headers);
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function respondWithError($data = array())
    {
        return $this->respond(
            array_merge(array('status' => 'ERROR'), $data)
        );
    }

    /**
     * @param $data
     * @return mixed
     */
    public function respondWithSuccess($data)
    {
        return $this->respond(
            array_merge(array('status' => 'OK'), $data)
        );
    }

    /**
     * @param string $message
     * @return mixed
     */
    public function respondNotFound($message = 'Not Found')
    {
        return $this->setStatusCode(Response::HTTP_NOT_FOUND)->respondWithError(array('message' => $message));
    }

    /**
     * @param string $message
     * @return mixed
     */
    public function respondWithNotSaved($message = 'Data Not Saved')
    {
        return $this->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR)->respondWithError(array('message' => $message));
    }

    /**
     * @param $data
     * @return mixed
     */
    public function respondWithCreated($data)
    {
        return $this->setStatusCode(Response::HTTP_CREATED)->respondWithSuccess($data);
    }

    /**
     * @param $data
     * @return mixed
     */
    public function respondWithSaved($data)
    {
        return $this->respondWithSuccess($data);
    }

    /**
     * @param $data
     * @return mixed
     */
    public function respondWithValidationError($data)
    {
        return $this->setStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY)->respondWithError($data);
    }
}
