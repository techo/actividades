<template>
    <div id="app-banner" v-if="show">
        <button class="banner-close" @click="dismiss" :aria-label="$t('frontend.app_banner_close')">&#x2715;</button>
        <img class="banner-icon" src="/img/icons/icon-128x128.png" alt="MiTECHO" />
        <div class="banner-info">
            <strong>MiTECHO</strong>
            <span>{{ $t('frontend.app_banner_subtitle') }}</span>
        </div>
        <a :href="storeUrl" class="banner-download" target="_blank" rel="noopener noreferrer">
            {{ $t('frontend.app_banner_download') }}
        </a>
    </div>
</template>

<script>
    export default {
        name: 'app-banner',
        props: {
            iosUrl: { type: String, default: '' },
            androidUrl: { type: String, default: '' },
        },
        data() {
            return {
                show: false,
                storeUrl: '',
            };
        },
        mounted() {
            if (localStorage.getItem('mitecho_app_banner_dismissed')) return;
            const ua = navigator.userAgent;
            const isIOS = /iPad|iPhone|iPod/.test(ua) && !window.MSStream;
            const isAndroid = /Android/.test(ua);
            if (isIOS && this.iosUrl) {
                this.storeUrl = this.iosUrl;
                this.show = true;
            } else if (isAndroid && this.androidUrl) {
                this.storeUrl = this.androidUrl;
                this.show = true;
            }
        },
        methods: {
            dismiss() {
                localStorage.setItem('mitecho_app_banner_dismissed', '1');
                this.show = false;
            },
        },
    };
</script>

<style scoped>
    #app-banner {
        display: flex;
        align-items: center;
        background-color: #f4a0a0;
        padding: 8px 12px;
        gap: 10px;
        position: relative;
        z-index: 1000;
    }

    .banner-close {
        background: transparent;
        border: none;
        font-size: 16px;
        color: #555;
        cursor: pointer;
        padding: 0 4px;
        line-height: 1;
        flex-shrink: 0;
    }

    .banner-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        flex-shrink: 0;
        object-fit: cover;
    }

    .banner-info {
        display: flex;
        flex-direction: column;
        flex: 1;
        min-width: 0;
    }

    .banner-info strong {
        font-size: 14px;
        color: #111;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .banner-info span {
        font-size: 12px;
        color: #444;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .banner-download {
        background-color: #007bff;
        color: #fff;
        border: none;
        border-radius: 20px;
        padding: 8px 18px;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        white-space: nowrap;
        flex-shrink: 0;
    }

    .banner-download:hover {
        background-color: #0056b3;
        color: #fff;
        text-decoration: none;
    }
</style>
