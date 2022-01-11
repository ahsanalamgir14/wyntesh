import request from '@/utils/request'

export function fetchCombos(query) {
    return request({
        url: '/user/combos',
        method: 'get',
        params: query
    })
}
export function getCombo(id) {
    return request({
        url: '/user/combo/' + id,
        method: 'get'
    })
}
export function getColorOfProducts(id) {
    return request({
        url: '/user/combo-color/Products/' + id,
        method: 'get'
    })
}

export function getSizeOfProducts(id) {
    return request({
        url: '/user/combo-size/Products/' + id,
        method: 'get'
    })
}

export function getProductsBySizeAndColor(category_id, size_id, color_id) {
    return request({
        url: '/user/products/category/' + category_id + '/size/' + size_id + '/color/' + color_id,
        method: 'get'
    })
}
export function placeCombo(data) {
    return request({
        url: '/user/combo/place',
        method: 'post',
        data
    })
}