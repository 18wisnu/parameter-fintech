<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body>
    <table>
        <!-- Header Perusahaan -->
        <tr>
            <th colspan="4" style="font-size: 16pt; font-weight: bold; text-align: center; height: 35px; background-color: #0284c7; color: #ffffff;">
                {{ strtoupper($businessName) }}
            </th>
        </tr>
        <tr>
            <th colspan="4" style="font-size: 13pt; font-weight: bold; text-align: center; height: 25px; background-color: #0369a1; color: #ffffff;">
                LAPORAN KEUANGAN & PEMBAGIAN HASIL
            </th>
        </tr>
        <tr>
            <td colspan="4" style="text-align: center; font-style: italic; color: #555555;">
                Periode: {{ $periodLabel }} (Dicetak: {{ $printedAt }})
            </td>
        </tr>
        <tr><td colspan="4"></td></tr>

        <!-- BAGIAN 1: PENDAPATAN -->
        <tr>
            <th colspan="3" style="background-color: #e0f2fe; color: #0369a1; font-weight: bold; font-size: 11pt; border-bottom: 2px solid #0284c7;">
                1. PENDAPATAN (REVENUE)
            </th>
            <th style="background-color: #e0f2fe; color: #0369a1; font-weight: bold; font-size: 11pt; text-align: right; border-bottom: 2px solid #0284c7;">
                NOMINAL (RP)
            </th>
        </tr>
        <tr>
            <td style="text-align: center; width: 40px;">1.1</td>
            <td colspan="2">Pendapatan Voucher Hotspot</td>
            <td style="text-align: right;">{{ number_format($voucherIncome, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td style="text-align: center;">1.2</td>
            <td colspan="2">Pendapatan Internet (PPPoE / Langganan)</td>
            <td style="text-align: right;">{{ number_format($pppoeIncome, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td style="text-align: center;">1.3</td>
            <td colspan="2">Pendapatan Pemasangan Baru (Registrasi / Instalasi)</td>
            <td style="text-align: right;">{{ number_format($pasangBaruIncome, 0, ',', '.') }}</td>
        </tr>
        @if($otherIncome > 0)
        <tr>
            <td style="text-align: center;">1.4</td>
            <td colspan="2">Pendapatan Lain-lain</td>
            <td style="text-align: right;">{{ number_format($otherIncome, 0, ',', '.') }}</td>
        </tr>
        @endif
        <tr>
            <td colspan="3" style="font-weight: bold; background-color: #f0fdf4; color: #166534; border-top: 1px solid #bbf7d0;">
                TOTAL PENDAPATAN
            </td>
            <td style="font-weight: bold; text-align: right; background-color: #f0fdf4; color: #166534; border-top: 1px solid #bbf7d0;">
                Rp {{ number_format($totalIncome, 0, ',', '.') }}
            </td>
        </tr>
        <tr><td colspan="4"></td></tr>

        <!-- BAGIAN 2: PENGELUARAN -->
        <tr>
            <th colspan="3" style="background-color: #fee2e2; color: #991b1b; font-weight: bold; font-size: 11pt; border-bottom: 2px solid #ef4444;">
                2. PENGELUARAN OPERASIONAL (EXPENSES)
            </th>
            <th style="background-color: #fee2e2; color: #991b1b; font-weight: bold; font-size: 11pt; text-align: right; border-bottom: 2px solid #ef4444;">
                NOMINAL (RP)
            </th>
        </tr>
        <tr>
            <td style="text-align: center;">2.1</td>
            <td colspan="2">Pengeluaran Tagihan (Bandwidth, Listrik, Air & Operasional)</td>
            <td style="text-align: right;">{{ number_format($tagihanExpense, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td style="text-align: center;">2.2</td>
            <td colspan="2">Pengeluaran Gaji Pegawai & Teknisi</td>
            <td style="text-align: right;">{{ number_format($gajiExpense, 0, ',', '.') }}</td>
        </tr>
        @if($otherExpense > 0)
        <tr>
            <td style="text-align: center;">2.3</td>
            <td colspan="2">Pengeluaran Lain-lain</td>
            <td style="text-align: right;">{{ number_format($otherExpense, 0, ',', '.') }}</td>
        </tr>
        @endif
        <tr>
            <td colspan="3" style="font-weight: bold; background-color: #fef2f2; color: #991b1b; border-top: 1px solid #fecaca;">
                TOTAL PENGELUARAN
            </td>
            <td style="font-weight: bold; text-align: right; background-color: #fef2f2; color: #991b1b; border-top: 1px solid #fecaca;">
                Rp {{ number_format($totalExpense, 0, ',', '.') }}
            </td>
        </tr>
        <tr><td colspan="4"></td></tr>

        <!-- BAGIAN 3: LABA BERSIH -->
        <tr>
            <th colspan="3" style="background-color: #0f172a; color: #ffffff; font-weight: bold; font-size: 12pt;">
                3. LABA BERSIH OPERASIONAL (NET PROFIT)
            </th>
            <th style="background-color: #0f172a; color: #38bdf8; font-weight: bold; font-size: 12pt; text-align: right;">
                Rp {{ number_format($netProfit, 0, ',', '.') }}
            </th>
        </tr>
        <tr><td colspan="4"></td></tr>

        <!-- BAGIAN 4: DANA CADANGAN -->
        <tr>
            <th colspan="3" style="background-color: #fef3c7; color: #92400e; font-weight: bold; font-size: 11pt;">
                4. ALOKASI DANA CADANGAN (TABUNGAN USAHA)
            </th>
            <th style="background-color: #fef3c7; color: #92400e; font-weight: bold; font-size: 11pt; text-align: right;">
                NOMINAL (RP)
            </th>
        </tr>
        <tr>
            <td style="text-align: center;">4.1</td>
            <td colspan="2">Persentase Dana Cadangan: <b>{{ $reservePercentage }}%</b></td>
            <td style="text-align: right; font-weight: bold; color: #b45309;">
                Rp {{ number_format($reserveFund, 0, ',', '.') }}
            </td>
        </tr>
        <tr>
            <td colspan="3" style="font-style: italic; color: #64748b;">
                Sisa Laba Bersih yang Siap Dibagikan ke Stakeholder (Distributable Profit)
            </td>
            <td style="text-align: right; font-weight: bold; color: #0284c7;">
                Rp {{ number_format($distributable, 0, ',', '.') }}
            </td>
        </tr>
        <tr><td colspan="4"></td></tr>

        <!-- BAGIAN 5: PEMBAGIAN HASIL -->
        <tr>
            <th colspan="4" style="background-color: #f1f5f9; color: #334155; font-weight: bold; font-size: 11pt; border-bottom: 2px solid #cbd5e1;">
                5. RINCIAN PEMBAGIAN HASIL STAKEHOLDER
            </th>
        </tr>
        <tr style="background-color: #f8fafc; font-weight: bold;">
            <td style="text-align: center; border: 1px solid #cbd5e1;">NO</td>
            <td style="border: 1px solid #cbd5e1;">NAMA STAKEHOLDER</td>
            <td style="text-align: center; border: 1px solid #cbd5e1;">PERSENTASE (%)</td>
            <td style="text-align: right; border: 1px solid #cbd5e1;">JATAH DIVIDEN (RP)</td>
        </tr>
        @forelse($shares as $index => $item)
        <tr>
            <td style="text-align: center; border: 1px solid #e2e8f0;">{{ $index + 1 }}</td>
            <td style="border: 1px solid #e2e8f0; font-weight: bold;">{{ $item['name'] }}</td>
            <td style="text-align: center; border: 1px solid #e2e8f0;">{{ $item['percentage'] }}%</td>
            <td style="text-align: right; border: 1px solid #e2e8f0; font-weight: bold;">
                Rp {{ number_format($item['amount'], 0, ',', '.') }}
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="4" style="text-align: center; font-style: italic; border: 1px solid #e2e8f0; color: #94a3b8;">
                Belum ada data stakeholder pembagian hasil.
            </td>
        </tr>
        @endforelse

        <tr><td colspan="4"></td></tr>
        <tr>
            <td colspan="2" style="color: #64748b; font-size: 9pt;">
                Generated by Parameter Fintech System
            </td>
            <td colspan="2" style="text-align: right; color: #64748b; font-size: 9pt;">
                Status: Resmi & Terverifikasi
            </td>
        </tr>
    </table>
</body>
</html>
