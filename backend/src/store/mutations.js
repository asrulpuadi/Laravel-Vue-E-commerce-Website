export function setUser(state,user){
    state.user.data = user
}

export function setToken(state,token){
    state.user.token = token

    if(token){
        sessionStorage.setItem('TOKEN',token)
    }else{
        sessionStorage.removeItem('TOKEN')
    }
}

export  function setProducts(state,[loading,response=null]){
    if (response) {
        state.products = {
            data : response.data,
            links : response.meta.links,
            total : response.meta.total,
            limit : response.meta.per_page,
            from : response.meta.from,
            to : response.meta.to,
            page : response.meta.current_page
        }
    }
    state.products.loading = loading
}

export  function setOrders(state,[loading,data=null]){
    if (data) {
        state.orders = {
            ...state.orders,
            data : data.data,
            links : data.meta.links,
            total : data.meta.total,
            limit : data.meta.per_page,
            from : data.meta.from,
            to : data.meta.to,
            page : data.meta.current_page
        }
    }
    state.orders.loading = loading
}

export function showToast(state,message){
    state.toast.show = true
    state.toast.message = message
}

export function hideToast(state){
    state.toast.show = false
    state.toast.message = ''
}