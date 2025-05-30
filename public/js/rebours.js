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
class Rebours 
{
    constructor(selected) {
        this.template = selected;
        this.render();
    }

    render() {
        let capitalize = (s) => s && String(s[0]).toUpperCase() + String(s).slice(1);
        let box = (key, val) => {
            return (
                `
                <div class="time">
                    <span class="${key}"> ${val} </span>
                    <small>${capitalize(key)}.</small>
                </div>
                `
            );
        };

        let deadline = new Date("Feb 1, 2025 15:00:00").getTime();
        let template = this.template;
        if(template != undefined) {
            let interval =setInterval(function() {
                let now = new Date().getTime();
                let t = deadline - now;
                let days = Math.floor(t / (1000 * 60 * 60 * 24));
                let hours = Math.floor((t%(1000 * 60 * 60 * 24))/(1000 * 60 * 60));
                let minutes = Math.floor((t % (1000 * 60 * 60)) / (1000 * 60));
                let seconds = Math.floor((t % (1000 * 60)) / 1000);
                template.innerHTML= `
                    <div class="time">
                        <span class="jours"> ${days} </span>
                        <small>Jours.</small>
                    </div>
                    <div class="time">
                        <span class="heurs"> ${hours} </span>
                        <small>Heurs.</small>
                    </div>
                    <div class="time">
                        <span class="minutes"> ${minutes} </span>
                        <small>Minutes.</small>
                    </div>
                    <div class="time">
                        <span class="secondes"> ${seconds} </span>
                        <small>Secondes.</small>
                    </div
                `;
                if (t < 0) {
                    clearInterval(interval);
                    template.innerHTML = "EXPIRED";
                }
            }, 1000);
        }
    }

    
}