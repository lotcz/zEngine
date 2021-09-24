var z = {
    
    getById : function(id) {
        return document.getElementById(id);
    },

    isString : function(s) {
        return  (typeof s === 'string' || s instanceof String);
    },

    isObject : function(o) {
        return (o !== null && typeof o === 'object');
    },

    getElement : function(idOrObject) {
        const el = (this.isString(idOrObject)) ? this.getById(idOrObject) : idOrObject;
        if (this.isObject(el)) {
            if (el.classList) {
                return el;
            }
        }
        return null;
    },

    addClass : function(idOrObject, cls) {
        const el = this.getElement(idOrObject);
        if (el) {
            el.classList.add(cls);
        }
    },

    removeClass : function(idOrObject, cls) {
        const el = this.getElement(idOrObject);
        if (el) {
            el.classList.remove(cls);
        }
    },

    show : function(idOrObject) {
        const el = this.getElement(idOrObject);
        if (el) {
            el.style.display = 'block';
        }
    },

    hide : function(idOrObject) {
        const el = this.getElement(idOrObject);
        if (el) {
            el.style.display = 'none';
        }
    },

    val : function(idOrObject) {
        const el = this.getElement(idOrObject);
        if (el) {
            return el.value;
        }
        return null;
    }

}