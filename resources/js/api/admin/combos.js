import request from '@/utils/request'

export function fetchList(query) {
    return request({
        url: '/admin/combos',
        method: 'get',
        params: query
    })
}

export function createCombo(data) {
    return request({
        url: '/admin/combos',
        method: 'post',
        data
    })
}

export function updateCombo(data, id) {
    return request({
        url: '/admin/combo/' + id + '/update',
        method: 'post',
        data
    })
}

export function changeComboStatus(data) {
    return request({
        url: '/admin/combo/change-status',
        method: 'post',
        data
    })
}

export function deleteCombo(id) {
    return request({
        url: '/admin/combo/' + id + '/delete',
        method: 'delete'
    })
}
export function categoryDelete(id) {
    return request({
        url: '/admin/combo-category/' + id + '/delete',
        method: 'delete'
    })
}