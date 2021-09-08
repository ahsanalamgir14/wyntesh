import request from '@/utils/request'

export function fetchList(query) {
    return request({
        url: '/admin/software-popups',
        method: 'get',
        params: query
    })
}

export function fetchSoftwarePopup(id) {
    return request({
        url: '/admin/software-popups/' + id,
        method: 'get'
    })
}

export function deleteSoftwarePopup(id) {
    return request({
        url: '/admin/software-popups/' + id + '/delete',
        method: 'delete'
    })
}


export function createSoftwarePopup(data) {
    return request({
        url: '/admin/software-popups',
        method: 'post',
        data,
        headers: { "Content-Type": "multipart/form-data" }
    })
}

export function updateSoftwarePopup(data) {
    return request({
        url: '/admin/software-popups/update',
        method: 'post',
        data,
        headers: { "Content-Type": "multipart/form-data" }
    })
}
export function getSoftwarePopup() {
    return request({
        url: '/software-popups',
        method: 'get'
    })
}