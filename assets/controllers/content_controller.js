import {Controller} from '@hotwired/stimulus';
import {getComponent} from '@symfony/ux-live-component';

export default class extends Controller {
    async connect() {
        this.component = await getComponent(this.element);
        this.component.on('render:finished', (component) => {
            feather.replace();
            const $element = $(this.element);
            if (!$element.visible()) {
                $([document.documentElement, document.body]).animate({
                    scrollTop: $element.offset().top
                }, 500);
            }
        });
    }
}
