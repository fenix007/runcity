function extend(obj1, obj2){
    for (var i in obj2) {
        if (obj1[i] && typeof(obj1[i]) == 'object') {
            extend(obj1[i], obj2[i])
        } else {
            obj1[i] = obj2[i];
        }
    }
}