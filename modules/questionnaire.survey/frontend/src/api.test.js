import { describe, it, expect, vi } from 'vitest'
import { submitSurvey, getSurvey } from './api'

describe('submitSurvey', () => {
    it('отправляет ответы на API', async () => {
        global.fetch = vi.fn(() =>
            Promise.resolve({
                json: () =>
                    Promise.resolve({
                        status: 'success',
                        data: 126,
                    }),
            })
        )

        const answers = {
            275: [223],
        }

        const result = await submitSurvey(346, answers)

        expect(fetch).toHaveBeenCalledWith(
            '/api/surveys/346/submit',
            {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    answers,
                }),
            }
        )

        expect(result).toEqual({
            status: 'success',
            data: 126,
        })
    })

    it('получает опрос с API', async () => {
        global.fetch = vi.fn(() =>
            Promise.resolve({
                json: () =>
                    Promise.resolve({
                        status: 'success',
                        data: {
                            ID: 346,
                            TITLE: 'Опрос',
                        },
                    }),
            })
        )

        const result = await getSurvey(346)

        expect(fetch).toHaveBeenCalledWith('/api/surveys/346')

        expect(result).toEqual({
            status: 'success',
            data: {
                ID: 346,
                TITLE: 'Опрос',
            },
        })
    })

})