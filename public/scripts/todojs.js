/**
 * @description      : Todo simple
 * @author           : christian shungu <christianshungu@gmail.com>
 * @group            : 
 * @created          : 07/10/2021 - 21:07:52
 * @version          : 1.0.0
 * 
 * 
 * MODIFICATION LOG
 * - Version         : 1.0.0
 * - Date            : 07/10/2021
 * - Author          : christian
 * - Modification    : 
**/
const app       = document.querySelector('.app-todos');
const todoForm  = app.querySelector('form');
const todoList  = app.querySelector('.todolist');
const todoInput = app.querySelector('form > input');
const todoTasks = app.querySelector('.todotasks');

let todos = [
    {
        titre: 'Faire du React',
        done: false,
        editMode: false
    },
    {
        titre: 'Faire du Javascript',
        done: true,
        editMode: false
    }
];

todoForm.addEventListener('submit', (e) => {
    e.preventDefault();
    const value = todoInput.value;
    todoInput.value = '';
    addTodo(value);
    
});
const displayTodo = () => {
    const nodes = todos.map((todo, index) => {
        if (todo.editMode) {
            return createEditTodo(todo, index);
        } else {
            return createTodo(todo, index);
        }
    });
    todoList.innerHTML= "";
    todoList.append(...nodes);
    if (todos.length > 0) {
        createTask();
    }
}
const createTodo = (todo, index) => {
    const li = document.createElement('li');
    const divLeft = document.createElement('div');
    divLeft.setAttribute("class", "item--left");
    const inputCheck = document.createElement('input');
    inputCheck.type = "checkbox";
    inputCheck.name = "check";
    inputCheck.setAttribute("class", "todo");
    inputCheck.checked = todo.done;
    inputCheck.addEventListener('click', (e) => {
        e.preventDefault();
        toggleTodo(index);
    });
    const labelCheck = document.createElement('label');
    labelCheck.innerText = todo.titre;
    divLeft.append(inputCheck, labelCheck);
    const divRight = document.createElement('div');
    divRight.setAttribute("class", "item--right");
    const btnEdit = document.createElement('button');
    btnEdit.setAttribute("class", `fas fa-pen ${todo.done ? 'done' : ''}`);
    btnEdit.setAttribute("title", "Edit");
    btnEdit.addEventListener("click", (e) => {
        e.stopPropagation();
        toggleEditMode(index);
    });
    const btnDelete = document.createElement('button');
    btnDelete.setAttribute("class", `fas fa-trash ${todo.done ? 'done' : ''}`);
    btnDelete.setAttribute("title", "Supprimer");
    btnDelete.addEventListener("click", (event) => {
        event.stopPropagation();
        deleteTodo(index);
    });
    if (todo.done) {
        btnEdit.setAttribute("disabled", `${todo.done ? "disabled" : ""}`);
        btnDelete.setAttribute("disabled", `${todo.done ? "disabled" : ""}`);
    }
    divRight.append(btnEdit, btnDelete);

    li.append(divLeft, divRight);

    return li; 
} 
const createEditTodo = (todo, index) => {
    const li = document.createElement('li');
    const divLeft = document.createElement('div');
    divLeft.setAttribute("class", "item--left");
    const input = document.createElement('input');
    input.type = "text";
    input.value = todo.titre;
    divLeft.append(input);

    const divRight = document.createElement('div');
    divRight.setAttribute("class", "item--right");
    const bntSave = document.createElement('button');
    bntSave.setAttribute("class", `fas fa-save ${todo.done ? 'done' : ''}`);
    bntSave.setAttribute("title", "Sauvegarder");
    bntSave.addEventListener('click', (e) => {
        e.stopPropagation();
        editTodo(index, input);
    });

    const bntCancel = document.createElement('button');
    bntCancel.setAttribute("class", `fas fa-times ${todo.done ? 'done' : ''}`); 
    bntCancel.setAttribute("title", "Annuler");
    bntCancel.addEventListener('click', (e) => {
        e.stopPropagation();
        toggleEditMode(index);
    });
    divRight.append(bntSave, bntCancel);
    li.append(divLeft, divRight);

    return li; 
} 
const addTodo = titre => {
    if (titre) {
        todos.push({titre, done: false});
    }
    displayTodo();
}
const deleteTodo = (index) => {
    todos.splice(index, 1);
    displayTodo();
};
const toggleTodo = index => { 
    todos[index].done = !todos[index].done;
    displayTodo();
}
const toggleEditMode = index => {
    todos[index].editMode = !todos[index].editMode;
    displayTodo();
};
const editTodo = (index , input) => {
    const value = input.value;
    todos[index].titre= value;
    todos[index].editMode = false;
    displayTodo();
};
const createTask = () => {
    let total = todos.length;
    let num = todos.filter(todo => todo.done === true).length;

    let tasksLeft = document.createElement("div");
    tasksLeft.setAttribute("class", "tasks--left");
    let span = document.createElement("span");
    span.innerText = `${num} de(s) ${total} tâches effectuée(s)`;
    tasksLeft.append(span);

    let tasksRight = document.createElement("div");
    tasksRight.setAttribute("class", "tasks--right");
    let btn = document.createElement("button");
    btn.innerText = "Supprimer de tâches cochées";
    btn.addEventListener("click", (e) => {
        e.stopPropagation();
        removeTasks();
    });
    tasksRight.append(btn);
    todoTasks.innerHTML = "";
    todoTasks.append(tasksLeft, tasksRight);
}
const removeTasks = () => {
    todos = todos.filter(todo => todo.done === false);
    displayTodo();
} 
displayTodo(); 