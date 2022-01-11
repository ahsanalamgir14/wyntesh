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