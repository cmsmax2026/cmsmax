<style>
    .mx-theme-menu-workspace {
        padding-bottom: 24px;
    }

    .mx-theme-menu-hero {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        gap: 18px;
        padding: 22px 24px;
        margin-bottom: 18px;
        border: 1px solid #e6edf7;
        border-radius: 20px;
        background: linear-gradient(135deg, #ffffff 0%, #f5f9ff 52%, #eef6ff 100%);
        box-shadow: 0 18px 35px rgba(15, 23, 42, 0.06);
    }

    .mx-theme-menu-hero__title {
        margin: 0 0 10px;
        font-size: 24px;
        font-weight: 700;
        color: #0f172a;
    }

    .mx-theme-menu-hero__desc {
        max-width: 760px;
        margin: 0;
        color: #475569;
        line-height: 1.75;
    }

    .mx-theme-menu-hero__meta {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        align-items: flex-start;
    }

    .mx-theme-menu-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 12px;
        border-radius: 999px;
        background: #fff;
        border: 1px solid #d7e4f5;
        color: #1e293b;
        font-size: 12px;
        font-weight: 600;
    }

    .mx-theme-menu-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.4fr) minmax(320px, .9fr);
        gap: 18px;
        align-items: start;
    }

    .mx-theme-menu-main,
    .mx-theme-menu-side {
        display: flex;
        flex-direction: column;
        gap: 18px;
    }

    .mx-theme-menu-card {
        border: 1px solid #e8edf5;
        border-radius: 18px;
        background: #fff;
        box-shadow: 0 16px 30px rgba(15, 23, 42, 0.05);
        overflow: hidden;
    }

    .mx-theme-menu-card__header {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        gap: 10px;
        padding: 16px 20px;
        border-bottom: 1px solid #edf2f7;
        background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
    }

    .mx-theme-menu-card__title {
        margin: 0;
        color: #0f172a;
        font-size: 16px;
        font-weight: 700;
    }

    .mx-theme-menu-card__desc {
        margin: 4px 0 0;
        color: #64748b;
        font-size: 12px;
        line-height: 1.7;
    }

    .mx-theme-menu-card__body {
        padding: 18px 20px 20px;
    }

    .mx-theme-menu-preview {
        display: flex;
        flex-direction: column;
        gap: 12px;
        padding: 18px;
        border: 1px dashed #cbd5e1;
        border-radius: 16px;
        background: #f8fbff;
    }

    .mx-theme-menu-preview__head {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        gap: 12px;
        align-items: center;
    }

    .mx-theme-menu-preview__title {
        display: flex;
        align-items: center;
        gap: 10px;
        color: #0f172a;
        font-size: 18px;
        font-weight: 700;
        line-height: 1.5;
    }

    .mx-theme-menu-preview__subtitle {
        color: #64748b;
        font-size: 13px;
        line-height: 1.8;
        word-break: break-all;
    }

    .mx-theme-menu-preview__meta {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
    }

    .mx-theme-menu-preview__item {
        padding: 10px 12px;
        border-radius: 12px;
        background: #fff;
        border: 1px solid #e2e8f0;
    }

    .mx-theme-menu-preview__label {
        display: block;
        margin-bottom: 4px;
        color: #94a3b8;
        font-size: 12px;
    }

    .mx-theme-menu-preview__value {
        color: #0f172a;
        font-size: 13px;
        font-weight: 600;
        line-height: 1.6;
        word-break: break-all;
    }

    .mx-theme-menu-tip-list {
        display: grid;
        gap: 10px;
        margin: 0;
    }

    .mx-theme-menu-tip {
        padding: 12px 14px;
        border-radius: 14px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        color: #475569;
        line-height: 1.7;
        font-size: 13px;
    }

    .mx-theme-menu-card .form-group:last-child {
        margin-bottom: 0;
    }

    @media (max-width: 1199px) {
        .mx-theme-menu-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 767px) {
        .mx-theme-menu-hero {
            padding: 18px;
        }

        .mx-theme-menu-card__body,
        .mx-theme-menu-card__header {
            padding-left: 16px;
            padding-right: 16px;
        }

        .mx-theme-menu-preview__meta {
            grid-template-columns: 1fr;
        }
    }
</style>
