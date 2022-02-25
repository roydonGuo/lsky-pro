<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StrategyRequest;
use App\Models\Strategy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StrategyController extends Controller
{
    public function index(Request $request): View
    {
        $strategies = Strategy::query()->when($request->query('keywords'), function (Builder $builder, $keywords) {
            $builder->where('name', 'like', "%{$keywords}%")->orWhere('intro', 'like', "%{$keywords}%");
        })->withCount('images')->withSum('images', 'size')->latest()->paginate();
        return view('admin.strategy.index', compact('strategies'));
    }

    public function add(): View
    {
        return view('admin.strategy.add');
    }

    public function edit(Request $request): View
    {
        /** @var Strategy $strategy */
        $strategy = Strategy::query()->findOrFail($request->route('id'));
        return view('admin.strategy.edit', compact('strategy'));
    }

    public function create(StrategyRequest $request): Response
    {
        $validated = $request->validated();
        $strategy = new Strategy($validated);
        DB::transaction(function () use ($strategy, $validated) {
            $strategy->save();
            $strategy->groups()->attach($validated['groups']);
        });
        return $this->success('创建成功');
    }

    public function update(StrategyRequest $request): Response
    {
        $validated = $request->validated();
        /** @var Strategy $strategy */
        $strategy = Strategy::query()->findOrFail($request->route('id'));
        $strategy->fill($request->validated());
        DB::transaction(function () use ($strategy, $validated) {
            $strategy->save();
            $strategy->groups()->sync($validated['groups']);
        });
        return $this->success('保存成功');
    }

    public function delete(Request $request): Response
    {
        if ($group = Strategy::query()->find($request->route('id'))) {
            $group->delete();
        }
        return $this->success('删除成功');
    }
}
