function $(selector) {
    return new DOMQuery(selector);
}

class DOMQuery {
    constructor(selector) {
        if (typeof selector === 'string') {
            this.elements = document.querySelectorAll(selector);
        } else if (selector instanceof Element) {
            this.elements = [selector];
        } else if (selector instanceof NodeList) {
            this.elements = Array.from(selector);
        } else {
            this.elements = [];
        }
    }

    // 遍历元素
    each(callback) {
        this.elements.forEach((element, index) => callback.call(element, index, element));
        return this;
    }

    // 添加类
    addClass(className) {
        return this.each((index, element) => element.classList.add(className));
    }

    // 移除类
    removeClass(className) {
        return this.each((index, element) => element.classList.remove(className));
    }

    // 切换类
    toggleClass(className) {
        return this.each((index, element) => element.classList.toggle(className));
    }

    // 设置或获取文本内容
    text(content) {
        if (content === undefined) {
            return this.elements[0]?.textContent || '';
        }
        return this.each((index, element) => element.textContent = content);
    }

    // 设置或获取 HTML 内容
    html(content) {
        if (content === undefined) {
            return this.elements[0]?.innerHTML || '';
        }
        return this.each((index, element) => element.innerHTML = content);
    }

    // 设置或获取 CSS 样式
    css(property, value) {
        if (value === undefined && typeof property === 'string') {
            return this.elements[0]?.style[property] || '';
        }
        if (typeof property === 'object') {
            return this.each((index, element) => {
                for (const key in property) {
                    element.style[key] = property[key];
                }
            });
        }
        return this.each((index, element) => element.style[property] = value);
    }

    // 绑定事件
    on(event, callback) {
        return this.each((index, element) => element.addEventListener(event, callback));
    }

    // 移除事件
    off(event, callback) {
        return this.each((index, element) => element.removeEventListener(event, callback));
    }

    // 查找子元素
    find(selector) {
        const foundElements = [];
        this.each((index, element) => {
            foundElements.push(...element.querySelectorAll(selector));
        });
        return new DOMQuery(foundElements);
    }

    // 获取或设置属性
    attr(name, value) {
        if (value === undefined) {
            return this.elements[0]?.getAttribute(name) || '';
        }
        return this.each((index, element) => element.setAttribute(name, value));
    }

    // 移除属性
    removeAttr(name) {
        return this.each((index, element) => element.removeAttribute(name));
    }

    // after 方法
    after(content) {
        return this.each((index, element) => {
            if (typeof content === 'string') {
                // 如果是字符串，直接插入 HTML
                element.insertAdjacentHTML('afterend', content);
            } else if (content instanceof Element || content instanceof NodeList || content instanceof Array) {
                // 如果是 DOM 元素、NodeList 或数组
                const nodes = content instanceof Element ? [content] : Array.from(content);
                nodes.forEach(node => {
                    element.parentNode.insertBefore(node, element.nextSibling);
                });
            } else if (content instanceof DOMQuery) {
                // 如果是 DOMQuery 对象
                content.elements.forEach(node => {
                    element.parentNode.insertBefore(node, element.nextSibling);
                });
            }
        });
    }

    // hide 方法
    hide() {
        return this.each((index, element) => {
            element.style.display = 'none';
        });
    }
    // show 方法
    show(display = 'block') {
        return this.each((index, element) => {
            element.style.display = display;
        });
    }

    // fadeIn 方法
    fadeIn(duration = 400, callback) {
        return this.each((index, element) => {
            // 确保元素初始状态为隐藏
            element.style.opacity = 0;
            element.style.display = 'block'; // 或者设置为元素默认的 display 值

            // 设置过渡效果
            element.style.transition = `opacity ${duration}ms`;

            // 触发浏览器重绘
            void element.offsetHeight; // 强制重绘

            // 开始淡入动画
            element.style.opacity = 1;

            // 动画结束后执行回调
            setTimeout(() => {
                callback?.call(element);
            }, duration);
        });
    }
    // fadeOut 方法
    fadeOut(duration = 400, callback) {
        return this.each((index, element) => {
            // 设置过渡效果
            element.style.transition = `opacity ${duration}ms`;

            // 开始淡出动画
            element.style.opacity = 0;

            // 动画结束后隐藏元素并执行回调
            setTimeout(() => {
                element.style.display = 'none';
                callback?.call(element);
            }, duration);
        });
    }
    // val 方法
    val(value) {
        if (value === undefined) {
            // 获取第一个元素的值
            return this.elements[0]?.value || '';
        } else {
            // 设置所有元素的值
            return this.each((index, element) => {
                element.value = value;
            });
        }
    }

}

// 插件扩展机制
DOMQuery.prototype.extend = function (name, fn) {
    DOMQuery.prototype[name] = fn;
};