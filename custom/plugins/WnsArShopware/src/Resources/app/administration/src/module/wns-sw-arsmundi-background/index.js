import './page/wns-sw-arsmundi-background-list';
import './page/wns-sw-arsmundi-background-create';
import deDE from './snippet/de-DE.json';
import enGB from './snippet/en-GB.json';


Shopware.Module.register('wns-sw-arsmundi-background', {
    type: 'plugin',
    name: 'ArsMundi',
    title: 'wns-arsmundi.general.title',
    description: 'wns-arsmundi.general.descriptionText',
    color: '#ff3d58',
    icon: 'default-object-image',
    favicon: 'icon-module-products.png',

    snippets: {
        'de-DE': deDE,
        'en-GB': enGB
    },

    routes: {
        list: {
            components: {
                default: 'wns-sw-arsmundi-background-list',
            },
            path: 'list',
        },
        create: {
            component: 'wns-sw-arsmundi-background-create',
            path: 'create',
            meta: {
                parentPath: 'wns.sw.arsmundi.background.list'
            },
        },
    },

    navigation: [{
        id:'wns-arsmundi-list',
        label: 'wns-arsmundi.general.title',
        color: '#ff68b4',
        path: 'wns.sw.arsmundi.background.list',
        icon: 'default-object-image',
        position: 22,
        parent: 'sw-content',
        privilege: 'arsmundi.viewer',
    }],
});
