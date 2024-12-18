import axiosClient from "../axios";
import state from "./state";

export function getCurrentUser({commit}){
    return axiosClient.get('/user')
        .then(response => {
            commit('setUser',response.data)
            return response
        })
}

export function login({commit},data){
    return axiosClient.post('/login',data)
        .then(({data})=>{
            commit('setUser',data.user)
            commit('setToken',data.token)
            return data
        })
}

export function logout({commit}){
    return axiosClient.post('/logout')
        .then((response)=>{
            commit('setToken',null)
            return response
        })
}

/* 
    ---- actions function for product ---- 
*/

export function getProducts({commit},{url=null,search='',perPage=10,sort_field,sort_direction}={}){
    commit('setProducts',[true])
    url = url || '/products' 
    return axiosClient.get(url,{
        params:{
            search,
            per_page:perPage,
            sort_field,
            sort_direction
        }
    })
    .then((response)=>{
        commit('setProducts',[false,response.data])
    })
    .catch(()=>{
        commit('setProducts',[false])
    })
}

export function getProduct({},id) {
    return axiosClient.get(`/products/${id}`)
    
}

export function  createProduct({commit}, product) {
    if (product.image instanceof File) {
        const form = new FormData();
        form.append('title', product.title);
        form.append('image', product.image);
        form.append('description', product.description);
        form.append('price', product.price);
        product = form;
    }
    return axiosClient.post('/products', product)
}

export function updateProduct({commit},product){
    const id = product.id

    if (product.image instanceof File) {
        const form = new FormData()

        form.append('id',product.id)
        form.append('title',product.title)
        form.append('image',product.image)
        form.append('description',product.description)
        form.append('price',product.price)
        form.append('_method','PUT')
        product = form
    }else{
        product._method = 'PUT'
    }

    return axiosClient.post(`/products/${id}`,product)
}

export function deleteProduct({commit},id) {
    return axiosClient.delete(`/products/${id}`)
}

/* 
    ----  end of actions function for product ---- 
*/

/* 
    ----  actions function for orders ---- 
*/

export function getOrders({commit},{url=null,search='',per_page,sort_field,sort_direction}={}){
    commit('setOrders',[true])
    url = url || '/orders'

    const params = {
        per_page:state.orders.limit
    }
    
    return axiosClient.get(url,{
        params:{
            ...params,
            search,
            per_page,
            sort_field,
            sort_direction
        }
    })
    .then((response)=>{
        commit('setOrders',[false,response.data])
    })
    .catch(()=>{
        commit('setOrders',[false])
    })
}

export function getOrder({},id) {
    return axiosClient.get(`/orders/${id}`)   
}

/* 
    ----  end of actions function for orders ---- 
*/

/*====== action users ======*/
export function getUsers({commit},{url=null,search='',perPage=10,sort_field,sort_direction}={}){
    commit('setUsers',[true])
    url = url || '/users' 
    return axiosClient.get(url,{
        params:{
            search,
            per_page:perPage,
            sort_field,
            sort_direction
        }
    })
    .then((response)=>{
        commit('setUsers',[false,response.data])
    })
    .catch(()=>{
        commit('setUsers',[false])
    })
}

export function getUser({},id) {
    return axiosClient.get(`/users/${id}`)
}

export function  createUser({commit}, user) {
    return axiosClient.post('/users', user)
}

export function updateUser({commit},user){
    return axiosClient.put(`/users/${user.id}`,user)
}

