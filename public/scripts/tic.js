const matrices = [
    ['.', '.', '.'],
    ['.', '.', '.'],
    ['.', '.', '.'],
];
const inputs    = document.querySelectorAll(' .tictactoe-ui>.row input');
const btnReset  = document.getElementById('resetID');
const printId   =  document.getElementById('printID');
let flag = 1;
let upfateFlag = () => {
    flag = (flag === 1) ? 0 : 1; 
    message(joueur(), 'ton tour');
}
let joueur = () => (flag === 1) ? 'X' : 'O';
let winner = () => {
    return (flag === 1) ? 'O' : 'X';
}
let message = (winner, text="a gagné", joueur="Joueur") => {
    let out = `${joueur} %winner% ${text}`
        .replace('%winner%', winner)
        .toString();
    printId.innerHTML = out;

}
let checkWinner = () => {
    for (let m = 0; m < 3;  m++) {
        if ((matrices[m][0] === matrices[m][1]) 
        && (matrices[m][1] === matrices[m][2]) 
        && (matrices[m][2] !== '.')) {
            return true;
        }
    }
    for (let n = 0; n < 3;  n++) {
        if ((matrices[0][n] === matrices[1][n]) 
        && (matrices[1][n] === matrices[2][n]) 
        && (matrices[2][n] !== '.')) {
            return true;
        }
    }
    if ((matrices[0][0] == matrices[1][1] ) 
    && (matrices[1][1]== matrices[2][2])
    && (matrices[2][2]!== '.')
    ) {
        return true;
    } else if ((matrices[0][2] == matrices[1][1] ) 
        && (matrices[1][1] == matrices[2][0])
        && (matrices[2][0] !== '.')) {
        return true;
    }
    return false; 
}
let checkTie = () => {
    let statut = false;
    for (let m = 0; m < 3; m++) {
        for (let n = 0; n < 3; n++) {
            if (matrices[m][n] === '.') {
                statut = true;
            }
       }
    }
    if (!statut && !checkWinner()) {
        message("Match nul,", "a recommancé", "");
        printId.style.color= "orange";
    }
}
let updateUi = () => {
    if (checkWinner()) { 
        inputs.forEach((input) => {
            input.disabled = true;
            if (input.value === winner()) {
                 input.style.color = "green";
            }
         });
        message(winner());
        printId.style.color= "green";
    }
}
inputs.forEach(input => {
    input.addEventListener('click', (event)=>  {
        event.preventDefault();
        event.stopImmediatePropagation();
        let target = event.target;
        let x  = target.getAttribute('x');
        let y  = target.getAttribute('y');
        if (flag === 1) {
            matrices[x][y] = 'X';
            target.value = 'X';
            target.disabled = true;
            upfateFlag();
        } else {
            matrices[x][y] = 'O';
            target.value = 'O';
            target.disabled = true;
            upfateFlag();
        }
        updateUi();
        checkTie();
    });
})
btnReset.addEventListener('click', (event) => {
    event.preventDefault();
    location.reload();
});