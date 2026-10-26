<?php

namespace App\Listeners;

use App\Events\GetEditor;
use App\Support\Theme\ShortTagParser;

/**
 * 在 Formtools 内容录入/编辑的富文本编辑器旁注入「插入短标签」按钮。
 *
 * 挂载方式：监听 App\Events\GetEditor 事件（该事件由 formtools 的 editor 字段渲染链触发，
 * 框架已预留此扩展点）。本监听器复用 Editor 模块的标准渲染产出编辑器 HTML，
 * 仅在其前面多拼一个按钮 + 一段脚本，从而：
 *   - 零改动 Formtools 模块核心（录入页、字段模板都没动）
 *   - 零改动 Editor 模块（直接 view() 复用 editor::admin.index.{driver}）
 *   - 通过 Event::listen(..., 100) 高优先级，使 hook('GetEditor')[0] 取到「带按钮的编辑器」
 *
 * 覆盖范围：editor 富文本字段（走本事件）+ 脚本在 DOM 就绪后扫描所有
 * textarea.form-control（含 formtype=textarea/code 字段）补挂同一按钮，
 * 对已有模型录入零风险。
 */
class EditorShortTagInjector
{
    public function handle(GetEditor $event)
    {
        $f = is_array($event->data) ? $event->data : (array) $event->data;
        $id = (string) ($f['identification'] ?? '');

        // 解析当前编辑器驱动（与 Editor 模块 resolveCurrentEditor 一致）
        $driver = (string) cacheGlobalSettingsByKey('editor', 'Editor');
        if ($driver === '') {
            $driver = 'tinymce';
        }
        $view = 'editor::admin.index.' . $driver;
        if (!view()->exists($view)) {
            $view = 'editor::admin.index.tinymce';
        }

        // 直接复用标准编辑器渲染，产出与原 GetEditor 链完全一致
        $editorHtml = view($view, ['f' => $f])->render();

        $bar = $id !== ''
            ? '<button type="button" class="btn btn-sm btn-outline-secondary mx-insert-shorttag" data-target="' . e($id) . '">插入短标签</button>'
            : '';

        return '<div class="mx-st-field mb-1">' . $bar . $editorHtml . $this->script() . '</div>';
    }

    /**
     * 插入器脚本：点击按钮弹出短标签清单，选中后插入到对应编辑器/文本框。
     * 用 window.__mxST 保证只注册一次（每个 editor 字段都会注入一份，但只生效一次）。
     */
    private function script(): string
    {
        $catalog = json_encode(ShortTagParser::catalog(), JSON_UNESCAPED_UNICODE);

        return <<<HTML
<script>
(function(){
  if(window.__mxST) return;
  window.__mxST = 1;
  var catalog = {$catalog};
  function insertTo(target, text){
    var ed = window.tinymce && window.tinymce.get(target);
    if(ed){ ed.insertContent(text); return; }
    var inst = window.__editorModuleInstances && window.__editorModuleInstances[target];
    if(inst && inst.editor){ inst.editor.insertText(text); return; }
    var ta = document.getElementById(target);
    if(ta && ta.setRangeText){ var s = ta.selectionStart || 0, e = ta.selectionEnd || 0; ta.setRangeText(text, s, e, 'end'); ta.focus(); }
  }
  function openPicker(btn){
    var target = btn.getAttribute('data-target');
    // 先清理上一次可能残留的弹窗（重复点击按钮不会叠加）
    var old = document.querySelector('.mx-st-pop');
    if(old && old.parentNode){ old.parentNode.removeChild(old); }
    var pop = document.createElement('div');
    pop.className = 'mx-st-pop';
    // 用 position:fixed：getBoundingClientRect() 返回的就是视口坐标，无需再加减滚动偏移；
    // 否则页面滚动后 absolute 定位（相对文档）会按视口坐标算，导致弹窗跑偏。
    pop.style.cssText = 'position:fixed;z-index:99999;background:#fff;border:1px solid #ddd;box-shadow:0 2px 8px rgba(0,0,0,.15);padding:6px;max-height:320px;overflow:auto;min-width:260px';
    catalog.forEach(function(item){
      var b = document.createElement('button');
      b.type = 'button';
      b.style.cssText = 'display:block;width:100%;text-align:left;margin:2px 0;padding:4px 8px;border:0;background:#f8f9fa;cursor:pointer;border-radius:3px';
      b.textContent = item.label + '  ' + item.example;
      b.onclick = function(e){ e.stopPropagation(); insertTo(target, item.example); if(pop.parentNode){ pop.parentNode.removeChild(pop); } };
      pop.appendChild(b);
    });
    var r = btn.getBoundingClientRect();
    pop.style.left = r.left + 'px';
    pop.style.top = (r.bottom + 4) + 'px';
    document.body.appendChild(pop);
    // 视口内夹紧：下方不够就翻到按钮上方，右侧溢出则左移
    var pr = pop.getBoundingClientRect();
    if(pr.bottom > window.innerHeight){ pop.style.top = (r.top - pop.offsetHeight - 4) + 'px'; }
    if(pr.right > window.innerWidth){ pop.style.left = (window.innerWidth - pop.offsetWidth - 8) + 'px'; }
    setTimeout(function(){
      document.addEventListener('click', function close(e){
        if(pop.parentNode && !pop.contains(e.target) && e.target !== btn){ pop.parentNode.removeChild(pop); document.removeEventListener('click', close); }
      });
    }, 0);
  }
  document.addEventListener('click', function(e){
    var btn = e.target.closest && e.target.closest('.mx-insert-shorttag');
    if(btn){ e.preventDefault(); openPicker(btn); }
  });
  function scan(){
    document.querySelectorAll('textarea.form-control').forEach(function(ta){
      if(document.querySelector('.mx-insert-shorttag[data-target="' + ta.id + '"]')) return;
      var wrap = document.createElement('div');
      wrap.className = 'mx-st-wrap mb-1';
      var btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'btn btn-sm btn-outline-secondary mx-insert-shorttag';
      btn.setAttribute('data-target', ta.id);
      btn.textContent = '插入短标签';
      ta.parentNode.insertBefore(wrap, ta);
      wrap.appendChild(btn);
      wrap.appendChild(ta);
    });
  }
  if(document.readyState !== 'loading'){ scan(); } else { document.addEventListener('DOMContentLoaded', scan); }
})();
</script>
HTML;
    }
}
