import {Controller} from '@hotwired/stimulus';
import * as Array from '../helpers/array';

export default class extends Controller {
    initQueryString() {
        this.element.__component.windowQueryString = window.location.search.substring(1);
    }

    connect() {
        this.element[this.identifier] = this;

        window.addEventListener('popstate', () => {
            this.initQueryString();
            this.component?.render();
        });

        const bindComponent = (component) => {
            this.component = component;
            this.bindRenderFinished();
        };

        this.element.addEventListener('live:connect', (event) => {
            bindComponent(event.detail.component);
        });

        if (this.element.__component) {
            bindComponent(this.element.__component);
        }
    }

    bindRenderFinished() {
        if (this._renderFinishedBound) {
            return;
        }
        this._renderFinishedBound = true;

        this.component.on('render:finished', (component) => {
            // we rerender component using browser history; so do not change history state
            if (this.element.__component.windowQueryString !== null) {
                return;
            }

            const queryString = component.element.__component?.queryString ?? '';

            let componentSearchParams = new URLSearchParams(queryString);
            let windowSearchParams = new URLSearchParams(window.location.search.substring(1));

            let windowEntries = [];
            let componentEntries = [];

            for (const [key, value] of componentSearchParams.entries()) {
                componentEntries[key] = value;
            }

            for (const [key, value] of windowSearchParams.entries()) {
                windowEntries[key] = value;
            }

            const itemsToRemove = Array.array_diff_key(windowEntries, componentEntries);
            const itemsToInsert = Array.array_diff_key(componentEntries, windowEntries);
            const itemsToUpdate = Array.array_intersect_key(componentEntries, windowEntries);

            for (const [key, value] of Object.entries(itemsToUpdate)) {
                windowSearchParams.set(key, value);
            }

            for (const [key, value] of Object.entries(itemsToRemove)) {
                if (key.startsWith(component.element.__component.componentName)) {
                    windowSearchParams.delete(key);
                }
            }

            for (const [key, value] of Object.entries(itemsToInsert)) {
                windowSearchParams.append(key, value);
            }

            const searchParams = windowSearchParams.toString();

            if (searchParams === '') {
                window.history.pushState({}, '', `${location.pathname}`);
            } else {
                window.history.pushState({}, '', `${location.pathname}?${searchParams}`);
            }

            this.element.__component.windowQueryString = null;
        });
    }
}
