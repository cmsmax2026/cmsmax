<?php

namespace App\Support\Theme;

use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\View\View;
use Stringable;

/**
 * 主题视图包装。
 *
 * 在不改动任何控制器的前提下，给 HomeView() / ModelView() 渲染出的 HTML
 * 增加「短标签解析」这一步，让主题作者可以在模板或后台内容里直接写
 * {mx:list model="news" limit="6" /} 这类标签。
 *
 * 对现有代码零侵入：控制器照常 return，Router 与 Response 都能正常处理。
 */
class ThemeView implements Htmlable, Renderable, Stringable
{
    protected View $view;

    protected bool $parseShortTags;

    public function __construct(View $view, bool $parseShortTags = true)
    {
        $this->view = $view;
        $this->parseShortTags = $parseShortTags;
    }

    /**
     * 关闭短标签解析（比如渲染 JSON / 邮件模板时）。
     */
    public function withoutShortTags(): self
    {
        $this->parseShortTags = false;

        return $this;
    }

    public function withShortTags(): self
    {
        $this->parseShortTags = true;

        return $this;
    }

    public function render(): string
    {
        // 把视图数据灌给短标签解析器，{mx:field name="title" /} 才能取到详情页当前记录。
        // 控制器 view($tpl, ['detailRecord' => ...]) 传的数据不在 View::getShared() 里。
        $previousContext = ShortTagParser::withContext((array) $this->view->getData());

        try {
            // 渲染和短标签解析都必须在上下文里完成，{mx:field} 依赖它
            return $this->parse($this->view->render());
        } finally {
            ShortTagParser::setContext($previousContext);
        }
    }

    private function parse(string $html): string
    {
        if (!$this->parseShortTags || $html === '') {
            return $html;
        }

        if (!ThemeManager::shortTagsEnabled()) {
            return $html;
        }

        return ShortTagParser::render($html);
    }

    public function toHtml(): string
    {
        return $this->render();
    }

    public function __toString(): string
    {
        try {
            return $this->render();
        } catch (\Throwable $exception) {
            if (function_exists('config') && config('app.debug')) {
                throw $exception;
            }

            return '';
        }
    }

    /**
     * 透传底层 View 的常用方法，保证现有写法不炸。
     */
    public function with($key, $value = null): self
    {
        $this->view->with($key, $value);

        return $this;
    }

    public function name($name = null)
    {
        return $this->view->name($name);
    }

    public function getData(): array
    {
        return $this->view->getData();
    }

    public function getView(): View
    {
        return $this->view;
    }

    /**
     * 未知方法转发给底层 View（如 renderSections 等）。
     */
    public function __call($method, $parameters)
    {
        return $this->view->{$method}(...$parameters);
    }
}
