@themeInclude('public.head')
@themeInclude('public.preloader')

<style>
    a, a:hover {
        text-decoration-line: none;
    }
</style>
<div class="page js-page ">
    @themeInclude('public.topMenu')
    @themeInclude('public.header')
    @include($template)
    <footer class="js-footer-is-fixed">
        @themeInclude('public.bottomMenu')
        <div class="footer">
            @themeInclude('public.footerMenu')
        </div>
    </footer>
</div>
@themeInclude('public.js')
</body>
</html>
