import { sgjs } from "../lib/sgjs.js";


sgjs().option({ selected: 'app-sgjs', frame: "cercle" , ladder: 10, Function: 'progress' , frontColor: "#ffa801", backColor: "#2c3e50"})
    .add({
        titre: 'Langage & Framework',
        frames: [
            { name: 'html/css', level: 5 },
            { name: 'angular', level: 9 },
            { name: 'javascript', level: 5 }
        ]
}).animate();