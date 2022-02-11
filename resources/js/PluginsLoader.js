class PluginsLoader {
    constructor(options, notificator) {
        this.options = options;
        this.notificator = notificator;
    }

    async registerPlugins() {
        let allButtons = this.getRequestedButtons();

        if (_.isEmpty(allButtons)) {
            return true;
        }

        return true;
    }

    getRequestedButtons() {
        const props = [
            'toolbarButtons',
            'toolbarButtonsMD',
            'toolbarButtonsSM',
            'toolbarButtonsXS',
        ];

        let buttons = [];

        for (let prop of props) {
            buttons.push(typeof this.options[prop] === 'undefined' ? null : this.options[prop]);
        }

        return buttons.flat(2);
    }

    errorPluginLoadNotification(name) {
        this.notificator.show(
            `Something wrong with ${name} plugin load. ` + 'Perhaps you forgot to publish it.',
            { type: 'error' }
        );
    }
}

export default PluginsLoader;
