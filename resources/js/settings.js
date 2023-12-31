import variables from '@/styles/element-variables.scss';

export default {
    /**
     * @type {String}
     */
    title: 'Wyntash',
    theme: variables.theme,
    totalLegs: 4,
    pvLabel: 'BV',
    baseUrl: 'https://business.wyntash.in/',
    // baseUrl: 'http://wyntash-soft.mlmsoftwaredemo.org/',
    // baseUrl: 'http://localhost:8000/',

    /**
     * @type {boolean} true | false
     * @description Whether show the settings right-panel
     */
    showSettings: false,

    /**
     * @type {boolean} true | false
     * @description Whether need tagsView
     */
    tagsView: true,

    /**
     * @type {boolean} true | false
     * @description Whether fix the header
     */
    fixedHeader: false,

    /**
     * @type {boolean} true | false
     * @description Whether show the logo in sidebar
     */
    sidebarLogo: true,

    /**
     * @type {string | array} 'production' | ['production','development']
     * @description Need show err logs component.
     * The default is only used in the production env
     * If you want to also use it in dev, you can pass ['production','development']
     */
    errorLog: 'production',
};