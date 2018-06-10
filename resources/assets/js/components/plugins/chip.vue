<template>
    <div class="col-md-3">
        <span class="chip">
            <span class="chip-content">
                {{ valor }}
                <span class="chip-close">
                    <a @click.prevent="remover" href="#">
                        <i class="icon fa fa-times-circle"></i>
                    </a>
                </span>
            </span>
        </span>
    </div>

</template>

<script>
    export default {
        name: "chip",
        props: ['valor', 'index'],
        methods: {
            remover: function () {
                Event.$emit('removerme', this.index);
            }
        }
    }
</script>

<style scoped>
    .chip {
        /*background-color: gray;*/
        /*padding-left: 4px;*/
        /*padding-right: 4px;*/
        /*border-radius: 5px;*/
        align-items: center;
        line-height: 2;
        background: #e0e0e0;
        color: rgba(0,0,0,.87);
        border-radius: 28px;
        border: 1px solid transparent;
        font-size: 13px;
        margin: 4px;
        outline: none;
        position: relative;
        padding-top: 5px;
        padding-bottom: 5px;
        transition: .3s cubic-bezier(.25,.8,.5,1);
    }

    .chip .chip-content {
        border-radius: 28px;
        vertical-align:middle;
        cursor: default;
        height: 32px;
        justify-content: space-between;
        padding: 5px 4px 5px 12px;
        white-space: nowrap;
        z-index: 1;
    }

    /*.chip-content {*/
        /*padding: 5px 4px 5px 12px;*/
    /*}*/

    .chip-close {
        color: inherit;
        vertical-align:middle;
        cursor: pointer;
        margin: 0 2px 0 8px;
        text-decoration: none;
    }

    .chip-close a {
        color: black;
    }

    .chip-close .icon {
        font-size: 20px;
        opacity: .5;
    }

    .chip-close .icon:hover {
        opacity: 1;
    }
</style>