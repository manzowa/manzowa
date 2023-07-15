/**
    * @description      : Compte à revours maintenance
    * @author           : christian shungu 
    * @group            : 
    * @created          : 14/07/2023 - 13:05:47
    * 
    * MODIFICATION LOG
    * - Version         : 1.0.0
    * - Date            : 14/07/2023
    * - Author          : christian shungu <
    * - Modification    : 
**/
class Rebours {
    constructor(selected) {
        this.template = selected;
        this.render();
    }
    render() {
        let deadline = new Date("Dec 5, 2023 15:37:25").getTime();
        let template = this.template;
        if(template != undefined) {
            let interval =setInterval(function() {
                let now = new Date().getTime();
                let t = deadline - now;
                let days = Math.floor(t / (1000 * 60 * 60 * 24));
                let hours = Math.floor((t%(1000 * 60 * 60 * 24))/(1000 * 60 * 60));
                let minutes = Math.floor((t % (1000 * 60 * 60)) / (1000 * 60));
                let seconds = Math.floor((t % (1000 * 60)) / 1000);
                template.innerHTML=days + "d " + hours + "h " + minutes + "m " + seconds + "s ";
                if (t < 0) {
                    clearInterval(interval);
                    template.innerHTML = "EXPIRED";
                }
            }, 1000);
        }
    }
}

const rebours = new Rebours(rebours_maintenance);