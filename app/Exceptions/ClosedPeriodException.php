<?php

namespace App\Exceptions;

/** Transaksi pada bulan yang sudah tutup buku tidak boleh ditambah, diubah atau dihapus. */
class ClosedPeriodException extends \DomainException
{
}
