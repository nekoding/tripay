<?php

namespace Nekoding\Tripay\Transactions;

use Illuminate\Support\Collection;

interface Transaction
{
    /**
     * @param array $data
     * @return $this
     */
    public function createTransaction(array $data): self;

    /**
     * @return Collection
     */
    public function getResponse(): Collection;

    /**
     * @param string $refNumber
     * @return $this
     */
    public function getDetailTransaction(string $refNumber): self;

    /**
     * @param array $data
     * @return string
     * @throws \Nekoding\Tripay\Exceptions\InvalidCredentialException
     */
    public function setSignatureHash(array $data): string;
}
