<?php

namespace MyCollection\app\dto;

use MiniPhpRest\core\utils\ResponseUtils;

class ResponsePropsObject
{

    private bool $result = false;
    private $data;

    private string $type = 'unknown';

    private string $errorMsg = 'Error unknown';
    private int $errCode = 1;

    public function __construct(
        bool $result = false,
        $data = null,
        string $type = 'unknown',
        string $errorMsg = 'Error unknown',
        int $errCode = 1
    ) {
        $this->result = $result;
        $this->data = $data;
        $this->type = $type;
        $this->errorMsg = $errorMsg;
        $this->errCode = $errCode;
    }

    public function isResult(): bool
    {
        return $this->result;
    }

    public function setResult(bool $result): ResponsePropsObject
    {
        $this->result = $result;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     * @return ResponsePropsObject
     */
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): ResponsePropsObject
    {
        $this->type = $type;
        return $this;
    }

    public function getErrorMsg(): string
    {
        return $this->errorMsg;
    }

    public function setErrorMsg(string $errorMsg): ResponsePropsObject
    {
        $this->errorMsg = $errorMsg;
        return $this;
    }

    public function getErrCode(): int
    {
        return $this->errCode;
    }

    public function setErrCode(int $errCode): ResponsePropsObject
    {
        $this->errCode = $errCode;
        return $this;
    }

    public function toArray(): array
    {
        return ResponseUtils::getDefaultResponseArray(
            $this->result,
            $this->data,
            $this->type,
            $this->errorMsg,
            $this->errCode
        );

    }

}