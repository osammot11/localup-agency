<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class LeadController extends Controller
{
    public function index(Request $request): Response
    {
        $sources = $this->sources();
        $filters = $this->filters($request);
        $search = trim($filters['q'] ?? '');
        $source = $filters['source'] ?? '';

        $leads = $this->filteredLeads($search, $source)
            ->addSelect(['meta_status' => DB::table('meta_events')
                ->select('status')
                ->whereColumn('lead_id', 'leads.id')
                ->latest('created_at')
                ->limit(1)])
            ->latest('id')
            ->paginate(20)
            ->withQueryString();
        $context = array_filter(['q' => $search, 'source' => $source, 'page' => $leads->currentPage()],
            fn (mixed $value): bool => $value !== '' && $value !== null);

        return response()->view('admin.leads', compact('leads', 'sources', 'search', 'source', 'context'))
            ->header('Cache-Control', 'private, no-store, max-age=0');
    }

    public function show(Request $request, Lead $lead): Response
    {
        $filters = $this->filters($request, true);
        $search = trim($filters['q'] ?? '');
        $source = $filters['source'] ?? '';
        $context = array_filter($filters, fn (mixed $value): bool => $value !== '' && $value !== null);
        $query = $this->filteredLeads($search, $source);

        abort_unless((clone $query)->whereKey($lead->id)->exists(), 404);

        $previousId = (clone $query)->where('leads.id', '>', $lead->id)->orderBy('leads.id')->value('leads.id');
        $nextId = (clone $query)->where('leads.id', '<', $lead->id)->orderByDesc('leads.id')->value('leads.id');
        $metaStatus = DB::table('meta_events')->where('lead_id', $lead->id)->latest('created_at')->value('status');

        return response()->view('admin.lead', compact('lead', 'previousId', 'nextId', 'metaStatus', 'context'))
            ->header('Cache-Control', 'private, no-store, max-age=0');
    }

    private function filteredLeads(string $search, string $source): Builder
    {
        return Lead::query()
            ->when($source !== '', fn (Builder $query) => $query->where('source', $source))
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    foreach (['nome', 'cognome', 'email', 'telefono', 'attivita'] as $column) {
                        $query->orWhere($column, 'like', '%'.$search.'%');
                    }
                });
            });
    }

    private function filters(Request $request, bool $withPage = false): array
    {
        $rules = [
            'q' => ['nullable', 'string', 'max:100'],
            'source' => ['nullable', 'string', 'in:'.implode(',', array_keys($this->sources()))],
        ];

        if ($withPage) {
            $rules['page'] = ['nullable', 'integer', 'min:1'];
        }

        return $request->validate($rules);
    }

    private function sources(): array
    {
        return [
            'social/ristoranti' => 'Ristoranti',
            'social/beauty' => 'Beauty',
            'social/antiquariato' => 'Antiquariato',
            'social/pasticcerie' => 'Pasticcerie',
            'siti' => 'Siti web',
        ];
    }
}
