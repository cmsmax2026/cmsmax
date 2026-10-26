<div id="content">
    <div class="container">
        @php
            $pageNum = data_get($model, 'home_config.page_num', $model['page_num'] ?? 0);
            $pagePosition = data_get($model, 'home_config.list_page_template', $model['list_page_template'] ?? 'center');
            $items = collect($data instanceof \Illuminate\Pagination\AbstractPaginator ? $data->items() : $data)
                ->map(fn ($item) => frontendRecordData($item))
                ->filter(fn ($item) => !empty($item['id']))
                ->values();
            $itemsById = $items->keyBy('id');
            $childrenByPid = [];
            foreach ($items as $item) {
                $pid = (int) ($item['pid'] ?? 0);
                $childrenByPid[$pid][] = $item;
            }
            $roots = $items->filter(function ($item) use ($itemsById) {
                $pid = (int) ($item['pid'] ?? 0);
                return $pid <= 0 || !$itemsById->has($pid);
            })->values();
            $flattenBranch = function (array $node, int $depth = 0) use (&$flattenBranch, $childrenByPid): array {
                $branch = [];
                foreach ($childrenByPid[$node['id']] ?? [] as $child) {
                    $child['_depth'] = $depth;
                    $branch[] = $child;
                    $branch = array_merge($branch, $flattenBranch($child, $depth + 1));
                }
                return $branch;
            };
        @endphp

        <div class="mx-list-shell">
            @themeInclude('public.listHero', ['model' => $model, 'listContext' => $listContext ?? []])

            <div class="mx-list-panel">
                @if($roots->isEmpty())
                    <div class="mx-empty mx-list-empty">{{ themeTrans('list.empty_tree') }}</div>
                @else
                    <div class="mx-tree-board">
                        @foreach($roots as $root)
                            @php($rootTitle = $root['title'] ?? ($root['name'] ?? themeTrans('common.content_no', ['id' => $root['id']])))
                            @php($branchItems = $flattenBranch($root))
                            <section class="mx-tree-column">
                                <div class="mx-tree-column__header">
                                    <div>
                                        <h3 class="mx-tree-column__title">{{$rootTitle}}</h3>
                                        <p class="mx-tree-column__desc">{{ \Illuminate\Support\Str::limit(trim(strip_tags($root['description'] ?? $root['content'] ?? '')), 96) ?: themeTrans('list.tree_desc') }}</p>
                                    </div>
                                    <a href="{{ model_detail_url($param['model'], $root['id']) }}" class="mx-tree-column__action">{{ themeTrans('common.view') }}</a>
                                </div>

                                @if($branchItems)
                                    <div class="mx-tree-list">
                                        @foreach($branchItems as $branchItem)
                                            @php($branchTitle = $branchItem['title'] ?? ($branchItem['name'] ?? themeTrans('common.content_no', ['id' => $branchItem['id']])))
                                            <a href="{{ model_detail_url($param['model'], $branchItem['id']) }}"
                                               class="mx-tree-item"
                                               style="--mx-tree-depth: {{ (int) ($branchItem['_depth'] ?? 0) }};">
                                                <span class="mx-tree-item__line"></span>
                                                <span class="mx-tree-item__title">{{$branchTitle}}</span>
                                                @if(!empty($branchItem['created_at']))
                                                    <span class="mx-tree-item__meta">{{$branchItem['created_at']}}</span>
                                                @endif
                                            </a>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="mx-empty">{{ themeTrans('home.empty_children') }}</div>
                                @endif
                            </section>
                        @endforeach
                    </div>
                @endif
            </div>

            @if ($pageNum > 0 && $data_source=="local")
                {{$data->appends($_GET)->links(theme_view('public.pagination'),['data'=>['side_num'=>2,'page_position'=>$pagePosition]])}}
            @endif
        </div>
    </div>
</div>

