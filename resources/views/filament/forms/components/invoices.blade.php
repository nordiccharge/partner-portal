@if ($getRecord()->invoices->count() > 0)
    <div class="text-center">
        <div style="--c-50:var(--warning-50);--c-400:var(--warning-400);--c-600:var(--warning-600);"
             class="fi-badge flex items-center justify-center gap-x-1 rounded-md text-xs font-medium ring-1 ring-inset px-2 min-w-[theme(spacing.6)] py-1 fi-color-custom bg-custom-50 text-custom-600 ring-custom-600/10 dark:bg-custom-400/10 dark:text-custom-400 dark:ring-custom-400/30">
            <span class="grid">
                <span class="truncate">
                    Invoices already exists for order!<br>Please check before creating new
                </span>
            </span>
        </div>
    </div><br>
    <table class="w-full text-sm fi-ta-table table-auto divide-y divide-gray-200 text-start dark:divide-white/5 rounded">
        <thead class="text-xs bg-gray-50 dark:bg-white/5">
            <tr>
                <th scope="col" class="px-6 py-3">ID</th>
                <th scope="col" class="px-6 py-3">Total Price</th>
                <th scope="col" class="px-6 py-3">Created At</th>
            </tr>
        </thead>
        <tbody class="">
        @foreach($getRecord()->invoices as $invoice)
            <tr class="fi-ta-row [@media(hover:hover)]:transition [@media(hover:hover)]:duration-75 hover:bg-gray-50 dark:hover:bg-white/5">
                <td  class="px-6 py-3"><span>{{ $invoice->id }}</span></td>
                <td  class="px-6 py-3"><span>{{ $invoice->total_price }}</span></td>
                <td  class="px-6 py-3"><span>{{ $invoice->created_at }}</span></td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endif
