<?php

namespace App\Http\Controllers\Network;

use Illuminate\Http\Request;

/** Reseller & outlet (mitra penjual voucher). Izin: entities.read. */
class EntityController extends NetworkController
{
    public const TYPES = ['reseller' => 'Reseller', 'outlet' => 'Outlet', 'isp' => 'Pusat'];

    public function index(Request $request)
    {
        $page = $this->remotePage('/v1/entities', $request, ['type', 'search']);

        return view('network.entities.index', compact('page') + ['types' => self::TYPES]);
    }

    public function show(int $id)
    {
        $entity = $this->client()->get("/v1/entities/{$id}");

        return view('network.entities.show', compact('entity') + ['types' => self::TYPES]);
    }
}
