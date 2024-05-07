<template>
    <div>
        <div class="card-header">
            <div class="row" v-if="!editando">
                <h5 class="col-md-9">
                    {{ data.header }}
                </h5>

                <div class="col-md-3 text-right">
                    <span>
                        <a @click="editCard" class="btn btn-light m-1">
                            <i class="fa fa-edit"></i>
                        </a>
                        <a @click="deleteCard" class="btn btn-light m-1">
                            <i class="fa fa-trash"></i>
                        </a>
                    </span>
                </div>
            </div>
            <div v-else>
                <label>{{ data.headerLabel }}</label>
                <input class="form-control" v-model="data.header" />
            </div>
        </div>
        <div class="card-body">

            <div class="card-title">
                <h5 v-if="!editando"> {{ data.title }} - {{ data.subTitle }}
                </h5>
                <div v-else>
                    <label>{{ data.titleLabel }}</label>
                    <input class="form-control" v-model="data.title" />
                    <label>{{ data.subTitleLabel }}</label>
                    <input class="form-control" v-model="data.subTitle" />
                </div>
            </div>
            <div class="card-text">
                <div class="row">
                    <p v-if="!editando" class="col-md-10">
                        {{ data.text }}
                    </p>
                    <div v-else class="col-md-12">
                        <label>{{ data.textLabel }}</label>
                        <textarea class="form-control" v-model="data.text">
                            {{ data.text }}
                        </textarea>
                    </div>
                </div>
                <div class="row justify-content-center mt-2">
                    <span v-if="(editando && !newCard)">
                        <a @click="saveCard" class="btn btn-primary text-white p-1 m-1">
                            <i class="fa fa-save"></i>
                            {{ $t('frontend.save') }}
                        </a>
                    </span>

                    <span v-if="newCard">
                        <a @click="saveCard" class="btn btn-primary text-white p-1 m-1">
                            <i class="fa fa-save"></i>
                            {{ $t('frontend.save') }}
                        </a>
                    </span>
                </div>
            </div>
        </div>
    </div>
</template>

<script>

export default {
    name: 'perfil',
    data: function () {
        var data = {
            editando: this.newCard,
            data: {
                id: this.identifier,
                header: this.header,
                headerLabel: this.headerLabel,
                title: this.title,
                titleLabel: this.titleLabel,
                subTitle: this.subTitle,
                subTitleLabel: this.subTitleLabel,
                text: this.text,
                textLabel: this.textLabel,
            },
        }
        return data;
    },
    props: {
        identifier: {
            type: Number,
            default: null
        },
        header: {
            type: String,
            default: ""
        },
        title: {
            type: String,
            default: ""
        },
        subTitle: {
            type: String,
            default: ""
        },
        text: {
            type: String,
            default: ""
        },
        headerLabel: {
            type: String,
            default: ""
        },
        titleLabel: {
            type: String,
            default: ""
        },
        subTitleLabel: {
            type: String,
            default: ""
        },
        textLabel: {
            type: String,
            default: ""
        },
        newCard: {
            type: Boolean,
            default: false
        },
    },
    mounted: function () {
        this.formDirty = false
    },
    watch: {

    },
    methods: {
        editCard: function () {
            this.editando = true;
        },
        saveCard: function () {
            if (this.data.id){
                this.$emit('saveCard', this.data);
            } else {
                this.$emit('createCard', this.data);
            }
            this.editando = false;
        },
        deleteCard: function () {
            this.$emit('deleteCard');
        },
    },
    computed: {
    }
}
</script>
