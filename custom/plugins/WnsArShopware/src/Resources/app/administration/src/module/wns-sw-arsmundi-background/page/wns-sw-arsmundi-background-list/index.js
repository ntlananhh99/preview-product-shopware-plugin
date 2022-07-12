import template from './wns-sw-arsmundi-background-list.html.twig';
const { Component, Mixin } = Shopware;
const { Criteria } = Shopware.Data;

Component.register('wns-sw-arsmundi-background-list', {
    template,
    inject: [
        'repositoryFactory',
        'numberRangeService',
        'filterFactory',],

    mixins: [
        Mixin.getByName('notification'),
        Mixin.getByName('listing'),
        Mixin.getByName('placeholder'),
    ],
    data() {
        return {
            backgrounds: null,
            showDeleteModal: false,
            sortBy: 'createdAt',
            sortDirection: 'DESC',
            isLoading: false,

        };
    },
    metaInfo() {
        return {
            title: this.$createTitle(),
        };
    },
    computed: {
        backgroundRepository() {
            return this.repositoryFactory.create('wns_image_background_upload');
        },
        backgroundColumns() {
            return [{
                property: 'media',
                dataIndex: 'media.fileName',
                label: this.$tc('wns-arsmundi.list.columnName.fileName'),
                allowResize: true,
                primary: true,
            }, {
                property: 'bgWidth',
                label: this.$tc('wns-arsmundi.list.columnName.width'),
                allowResize: true,
                align: 'center',
            },{
                property: 'media.fileSize',
                label: this.$tc('wns-arsmundi.list.columnName.fileSize'),
                allowResize: true,
                align: 'center',
            }, {
                property: 'active',
                label: this.$tc('wns-arsmundi.list.columnName.active'),
                allowResize: true,
                align: 'center',
            },];
        },

        backgroundCriteria() {
            const backgroundCriteria = new Criteria(this.page, this.limit);
            backgroundCriteria.addAssociation('media')
                .addSorting(Criteria.sort(this.sortBy, this.sortDirection));;
            if (this.term) {
                backgroundCriteria.addFilter(Criteria.multi('OR', [
                    Criteria.contains('media.fileName', this.term),
                    Criteria.contains('media.fileSize', this.term),
                ]));
            }
            return backgroundCriteria;
        },
    },
    methods: {
        onEdit(id,isEdit) {
            if (isEdit == false) {
                return {name: 'wns.sw.arsmundi.background.detail', params: {id: id}}
            }
            else {
                return this.$router.push({ name: 'wns.sw.arsmundi.background.detail', params: { id: id } });
            }
        },
        async getList() {
            this.isLoading = true;
            await this.backgroundRepository.search(this.backgroundCriteria, Shopware.Context.api)
                .then((searchResult) => {
                    this.total = searchResult.total;
                    this.backgrounds = searchResult;
                    this.isLoading = false;
                });
        },

        onChangeLanguage() {
            this.getList();
        },

        updateTotal({ total }) {
            this.total = total;
        },
    },

});
