/**
    * @name             : sgjs
    * @description      : Skills Grid
    * @author           : christian
    * @created          : 05/09/2021 - 16:27:44
    * @version          : 1.0.0
    * 
**/
 "use strict";

 export const sgjs = function () {
    let _o = {};
    const _setProperty = (name, value) => {
        let root = document.documentElement;
        root.style.setProperty(name, value);
    };
    const _CallFunction = (callback ) => {
        _o.collections.forEach((collection, index) => {
            if (collection) {
                let pos = 0;
                let taille =  collection.frames.length-1;
                let panel = new JPanel;
                panel.append(new JTitre(collection.titre));
                let repeat = () => {
                    let jcontour = JContour.Init(collection.frames[pos].name);
                    panel.append(jcontour);
                    let bindCallback = callback.bind(_animate);
                    _o.selected.appendChild(panel.gElement());
                    if (taille == pos) {
                        clearInterval(intervalID);
                    } 
                    bindCallback(collection, pos);
                    pos++;
                };
                let intervalID = setInterval(repeat, 2000);
            }
        }); 
    };
    class ValidationElement extends Error {
        constructor(message) {
            super(message);
            this.name = "ValidationElement";
        }
    }
    class OptionError extends Error {
        constructor(message) {
            super(message);
            this.name = "OptionError";
        }
    }
    class JElement {
        eOut;
        eClasse;
        eStyle;
        constructor(tag, eClasse) {
            try {
                this.eOut = document.createElement(tag);
                this.eStyle = "";
                this.eClasse = eClasse;
            } catch (err) {
                throw new ValidationElement("Problem with the html floor");
            }
        }
        addClass(classe){
            this.eOut.classList.add(classe);
            return this;
        }
        replaceClass (oClasse, nClasse) {
            this.eOut.classList.replace(oClasse, nClasse);
            return this;
        };
        sNode(node){
            this.eOut.innerHTML = node.toString();
            return this;
        }
        attr (name, value) {
            this.eOut.setAttribute(name, value);
            return this;
        }
        sData(cle, value){
            let key = `data-${cle}`;
            this.attr(key, value);
            return this;
        }
        css(k, s) {
            this.eStyle += k+':'+s+';';
            return this;
        }
        append(node) {
            this.eOut.append(node?.gElement());
            return this;
        }
        gData(k) {
            let r = null;
            if (k != null || k != undefined) {
                r = this.eOut.dataset.k;
            }
            return r;
        }
        gElement() {
            this.addClass(this.eClasse);
            if (this.eStyle.length-1 !== -1){
                this.attr('style', this.eStyle);
            }
            return this.eOut;
        }
    }
    class JPanel extends JElement {
        constructor() {
            super('div', 'jc-panel');
        }
    }
    class JWrap extends JElement {
        constructor() {
            super('div', 'jc-wrap');
        }
        static Init() {
            let that = new this();
            let frame;
            switch (_o.Function) {
                case "progress": {
                    frame = JProgress.Init();
                    that.append(frame);
                    break;
                }
                default: {
                    let counter = 0;
                    while (_o.ladder > counter) {
                        frame = new JFrame();
                        that.append(frame);
                        counter++;
                    }
                    break; 
                } 
            }
            return that;
        }
    }
    class JTitre extends JElement {
        constructor(titre) {
            super('h4', 'jc-titre');
            this.sNode(titre);
        }
    }
    class JFrame extends JElement {
        constructor() {
            super('div', 'jc-frame');
        }
    }
    class JLabel extends JElement {
        constructor(label) {
            super('div', 'jc-label');
            this.sNode(label);
        }
    }
    class JContour extends JElement {
        constructor() {
            super('div', 'jc-contour');
        }
        static Init(name) {
            let jwrap = JWrap.Init();
            let that = new this();
                that
                .append(new JLabel(name))
                .append(jwrap);
            return that;
        }
    }
    class JBar extends JElement {
        constructor() {
            super('div', 'jc-bar');
        }
        static Init() {
            let that = new this();
            that.append(
                new JElement('div', 'jc-tooltip')
                    .append(new JElement('div', 'jc-tooltiptext'))
            );
            return that;
        }
    }
    class JProgress extends JElement {
        constructor() {
            super('div', 'jc-progress');
        }
        static Init() {
            let that = new this();
                that.append(JBar.Init());
            return that;
        }
    }
    const _animate = {
 
        show(collection, index) {
            let jcontours = document.getElementsByClassName('jc-contour')?? false;
        
            if (jcontours) {
                let jcontour = jcontours[index];
                let jwrap    = jcontour.querySelector('.jc-wrap');
                let jframes  = jwrap.querySelectorAll('.jc-frame');
              
                let o = {
                    left: _o.width + _o.space,
                    cardinal: jframes.length - 1,
                    x: { speed: _o.speed / 8, id: 0, index: 0 },
                    y: { speed: _o.speed / 4, id: 0, index: 0 }
                };
     
                o.x.id = setInterval(() => {
                    let ml = o.left * o.x.index;
                    let figure = `jc-${_o.frame}`;
                    jframes[o.x.index].classList.add(figure);
                    jframes[o.x.index].style.marginLeft = ml + "px";

                    if (o.x.index === o.cardinal) {
                        clearInterval(o.x.id);
                        let oFrame = collection.frames[index];
                        let level = oFrame.level - 1;
                        o.y.id = setInterval(() => {
                            jframes[o.y.index].classList.add("jc-level");
                            if (level === o.y.index) {
                                clearInterval(o.y.id);
                            }
                            o.y.index++;
                        }, o.y.speed);
                    }
                    o.x.index++;
                }, o.x.speed); 
            }
        },
        progress(collection, index) {
            let jcontours = document.getElementsByClassName('jc-contour')?? false;

            if (jcontours) {
                let o = {
                    x: { id: 0, index: 10},
                    y: { id: 0, index: 0}
                };
    
                let oFrame       = collection.frames[index];
                let width        = 10 * oFrame?.level;
                let level        = oFrame?.level;
                let jcontour     = jcontours[index];
                let jprogress    = jcontour.querySelector(".jc-progress");
                let jbar         = jprogress.querySelector(".jc-bar");
                let jtooltip     = jbar.querySelector(".jc-tooltip");
                let jtooltiptext = jtooltip.querySelector(".jc-tooltiptext");
                jtooltiptext.innerText = level;

                o.x.id = setInterval(() => {
                    jbar.style.width= o.x.index+ "%";
                    if (o.x.index === width) {
                        clearInterval(o.x.id);
                    }
                    o.x.index = o.x.index + 10;
                }, 100);
            }        
           
        }
    }
    return {
        option(o) {
            let picked = document.getElementById(o.selected);
            if (picked) {
                _o = {
                    selected: picked,
                    speed: o.speed ?? 1000,
                    ladder: o.ladder ?? 10,
                    frame: o.frame ?? "cercle",
                    frontColor: o.frontColor ?? "#74b9ff",
                    backColor: o.backColor ?? "#2c3e50",
                    width: o.width ?? 24,
                    space: o.space ?? 4,
                    counter: 0,
                    collections: [],
                    Function: o.Function?? "show"
                };
                return this;
            } else {
                throw new  OptionError("L'option selected est obligatoire");
            }
        },
        add(collection) {
            if (collection.titre) {
                let frames = collection?.frames;
                let arr = frames.filter(frame => frame.level > _o.ladder);
                if (arr.length > 0) {
                    let msg;
                    msg = "L'une d'option leve est supérieur à l'option ladder";
                    throw new  OptionError(msg);
                } else {
                    _o.collections.push(collection);
                    _o.counter++;
                    _setProperty("--jc-front-color", _o.frontColor);
                    _setProperty("--jc-back-color", _o.backColor);
                }
            } else {
                throw new  OptionError("L'option titre est obligatoire");
            }
            return this
        },
        animate() {
            let animateFunc =_o.Function;
            switch (animateFunc) {
                case "progress": {
                    _CallFunction(_animate.progress);
                    break;
                }
                default: {
                    _CallFunction(_animate.show);
                    break;
                }
            }
        }
    };
};