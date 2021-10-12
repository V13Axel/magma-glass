require('./bootstrap');

import app from './magma-glass/app';
import fileTree from './magma-glass/file-tree';

import Alpine from 'alpinejs';
window.Alpine = Alpine;

Alpine.data('app', app);
Alpine.data('fileTree', fileTree);

Alpine.start();

// Hotkeys
import hotkeys from 'hotkeys-js';

hotkeys('ctrl+k', function(event, handler){
    event.preventDefault();
    window.dispatchEvent(new Event('focus-search'));
});

hotkeys('ctrl+shift+l', function(event, handler){
    event.preventDefault();
    window.toggleTheme();
});

hotkeys('ctrl+f5,ctrl+shift+r', function(event, handler){
    event.preventDefault();
    let currentItem;
    let currentKey;
    let toRemove = [];

    for (let i = 0; i < localStorage.length; i++){
        currentItem = localStorage.getItem(localStorage.key(i));
        currentKey = localStorage.key(i);

        if (currentItem.includes("expiry")) {
            toRemove.push(currentKey);
        }
    }

    for (let i = 0; i < toRemove.length; i++) {
        localStorage.removeItem(toRemove[i]);
    }

    location.reload();
});
