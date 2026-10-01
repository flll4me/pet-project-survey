import { render, screen, cleanup } from '@testing-library/react'
import { describe, it, expect, afterEach, vi } from 'vitest'
import '@testing-library/jest-dom/vitest'
import App from './App'
import { submitSurvey, getSurvey } from './api'

vi.mock('./api', () => ({
    submitSurvey: vi.fn(() =>
        Promise.resolve({
            status: 'success',
            data: 126,
        })
    ),

    getSurvey: vi.fn(() =>
        Promise.resolve({
            status: 'success',
            data: {
                ID: 346,
                TITLE: 'Опрос',
                QUESTIONS: [],
            },
        })
    ),
}))

afterEach(() => {
    cleanup()
})

describe('App', () => {
    it('показывает название опроса', () => {
        const survey = {
            TITLE: 'Опрос по программированию',
            QUESTIONS: [],
        }

        render(<App survey={survey} />)

        expect(
            screen.getByText('Опрос по программированию')
        ).toBeInTheDocument()
    })

    it('показывает вопросы опроса', () => {
        const survey = {
            TITLE: 'Опрос по программированию',
            QUESTIONS: [
                { ID: 1, TITLE: 'Какой язык вы знаете?' },
                { ID: 2, TITLE: 'Сколько лет вы программируете?' },
            ],
        }

        render(<App survey={survey} />)

        expect(
            screen.getByText('Какой язык вы знаете?')
        ).toBeInTheDocument()

        expect(
            screen.getByText('Сколько лет вы программируете?')
        ).toBeInTheDocument()
    })

    it('показывает варианты ответа для вопроса single', () => {
        const survey = {
            TITLE: 'Опрос',
            QUESTIONS: [
                {
                    ID: 1,
                    TITLE: 'Какой язык вы знаете?',
                    TYPE: 'single',
                    OPTIONS: [
                        { ID: 1, TITLE: 'PHP' },
                        { ID: 2, TITLE: 'JavaScript' },
                    ],
                },
            ],
        }

        render(<App survey={survey} />)

        expect(screen.getByText('PHP')).toBeInTheDocument()
        expect(screen.getByText('JavaScript')).toBeInTheDocument()
    })

    it('показывает поле ввода для вопроса text', () => {
        const survey = {
            TITLE: 'Опрос',
            QUESTIONS: [
                {
                    ID: 1,
                    TITLE: 'Расскажите о себе',
                    TYPE: 'text',
                },
            ],
        }

        render(<App survey={survey} />)

        expect(
            screen.getByRole('textbox')
        ).toBeInTheDocument()
    })

    it('показывает варианты ответа для вопроса multiple', () => {
        const survey = {
            TITLE: 'Опрос',
            QUESTIONS: [
                {
                    ID: 1,
                    TITLE: 'Какие языки вы знаете?',
                    TYPE: 'multiple',
                    OPTIONS: [
                        { ID: 1, TITLE: 'PHP' },
                        { ID: 2, TITLE: 'JavaScript' },
                    ],
                },
            ],
        }

        render(<App survey={survey} />)

        expect(
            screen.getAllByRole('checkbox')
        ).toHaveLength(2)
    })

    it('позволяет выбрать вариант ответа single', () => {
        const survey = {
            TITLE: 'Опрос',
            QUESTIONS: [
                {
                    ID: 1,
                    TITLE: 'Какой язык вы знаете?',
                    TYPE: 'single',
                    OPTIONS: [
                        { ID: 1, TITLE: 'PHP' },
                        { ID: 2, TITLE: 'JavaScript' },
                    ],
                },
            ],
        }

        render(<App survey={survey} />)

        const php = screen.getByRole('radio', { name: 'PHP' })

        php.click()

        expect(php).toBeChecked()
    })

    it('позволяет выбрать несколько вариантов multiple', () => {
        const survey = {
            TITLE: 'Опрос',
            QUESTIONS: [
                {
                    ID: 1,
                    TITLE: 'Какие языки вы знаете?',
                    TYPE: 'multiple',
                    OPTIONS: [
                        { ID: 1, TITLE: 'PHP' },
                        { ID: 2, TITLE: 'JavaScript' },
                    ],
                },
            ],
        }

        render(<App survey={survey} />)

        const checkboxes = screen.getAllByRole('checkbox')

        checkboxes[0].click()
        checkboxes[1].click()

        expect(checkboxes[0]).toBeChecked()
        expect(checkboxes[1]).toBeChecked()
    })

    it('позволяет ввести текстовый ответ', () => {
        const survey = {
            TITLE: 'Опрос',
            QUESTIONS: [
                {
                    ID: 1,
                    TITLE: 'Расскажите о себе',
                    TYPE: 'text',
                },
            ],
        }

        render(<App survey={survey} />)

        const input = screen.getByRole('textbox')

        input.value = 'Я изучаю React'

        expect(input).toHaveValue('Я изучаю React')
    })

    it('у варианта ответа есть его ID в value', () => {
        const survey = {
            TITLE: 'Опрос',
            QUESTIONS: [
                {
                    ID: 1,
                    TITLE: 'Какой язык вы знаете?',
                    TYPE: 'single',
                    OPTIONS: [
                        { ID: 10, TITLE: 'PHP' },
                        { ID: 20, TITLE: 'JavaScript' },
                    ],
                },
            ],
        }

        render(<App survey={survey} />)

        const php = screen.getByRole('radio', { name: 'PHP' })

        expect(php).toHaveAttribute('value', '10')
    })

    it('у checkbox есть ID варианта в value', () => {
        const survey = {
            TITLE: 'Опрос',
            QUESTIONS: [
                {
                    ID: 1,
                    TITLE: 'Какие языки вы знаете?',
                    TYPE: 'multiple',
                    OPTIONS: [
                        { ID: 10, TITLE: 'PHP' },
                        { ID: 20, TITLE: 'JavaScript' },
                    ],
                },
            ],
        }

        render(<App survey={survey} />)

        const php = screen.getByRole('checkbox', { name: 'PHP' })

        expect(php).toHaveAttribute('value', '10')
    })

    it('обязательный текстовый вопрос имеет required', () => {
        const survey = {
            TITLE: 'Опрос',
            QUESTIONS: [
                {
                    ID: 1,
                    TITLE: 'Расскажите о себе',
                    TYPE: 'text',
                    REQUIRED: true,
                },
            ],
        }

        render(<App survey={survey} />)

        const input = screen.getByRole('textbox')

        expect(input).toBeRequired()
    })

    it('показывает кнопку отправки', () => {
        const survey = {
            TITLE: 'Опрос',
            QUESTIONS: [],
        }

        render(<App survey={survey} />)

        expect(
            screen.getByRole('button', { name: 'Отправить' })
        ).toBeInTheDocument()
    })

    it('отправляет форму при нажатии на кнопку', () => {
        const survey = {
            TITLE: 'Опрос',
            QUESTIONS: [],
        }

        render(<App survey={survey} />)

        const form = screen.getByRole('form')
        const button = screen.getByRole('button', { name: 'Отправить' })

        expect(form).toContainElement(button)
    })

    it('отменяет стандартную отправку формы', () => {
        const survey = {
            TITLE: 'Опрос',
            QUESTIONS: [],
        }

        render(<App survey={survey} />)

        const form = screen.getByRole('form')

        const submitEvent = new Event('submit', {
            bubbles: true,
            cancelable: true,
        })

        form.dispatchEvent(submitEvent)

        expect(submitEvent.defaultPrevented).toBe(true)
    })

    it('собирает ответ для single вопроса', () => {
        const survey = {
            TITLE: 'Опрос',
            QUESTIONS: [
                {
                    ID: 1,
                    TITLE: 'Какой язык знаешь?',
                    TYPE: 'single',
                    REQUIRED: true,
                    OPTIONS: [
                        { ID: 10, TITLE: 'PHP' },
                        { ID: 20, TITLE: 'JavaScript' },
                    ],
                },
            ],
        }

        render(<App survey={survey} />)

        const php = screen.getByLabelText('PHP')
        php.click()

        const form = screen.getByRole('form')

        const submitEvent = new Event('submit', {
            bubbles: true,
            cancelable: true,
        })

        form.dispatchEvent(submitEvent)

        expect(submitEvent.defaultPrevented).toBe(true)
    })

    it('передаёт собранный ответ в onSubmit', () => {
        const survey = {
            TITLE: 'Опрос',
            QUESTIONS: [
                {
                    ID: 1,
                    TITLE: 'Какой язык знаешь?',
                    TYPE: 'single',
                    REQUIRED: true,
                    OPTIONS: [
                        { ID: 10, TITLE: 'PHP' },
                        { ID: 20, TITLE: 'JavaScript' },
                    ],
                },
            ],
        }

        const handleSubmit = vi.fn()

        render(
            <App
                survey={survey}
                onSubmit={handleSubmit}
            />
        )

        const php = screen.getByLabelText('PHP')
        php.click()

        const form = screen.getByRole('form')

        const submitEvent = new Event('submit', {
            bubbles: true,
            cancelable: true,
        })

        form.dispatchEvent(submitEvent)

        expect(handleSubmit).toHaveBeenCalledWith({
            1: 10,
        })
    })

    it('передаёт несколько ответов для multiple вопроса', () => {
        const survey = {
            TITLE: 'Опрос',
            QUESTIONS: [
                {
                    ID: 1,
                    TITLE: 'Какие языки знаешь?',
                    TYPE: 'multiple',
                    REQUIRED: false,
                    OPTIONS: [
                        { ID: 10, TITLE: 'PHP' },
                        { ID: 20, TITLE: 'JavaScript' },
                        { ID: 30, TITLE: 'Python' },
                    ],
                },
            ],
        }

        const handleSubmit = vi.fn()

        render(
            <App
                survey={survey}
                onSubmit={handleSubmit}
            />
        )

        screen.getByLabelText('PHP').click()
        screen.getByLabelText('JavaScript').click()

        const form = screen.getByRole('form')

        const submitEvent = new Event('submit', {
            bubbles: true,
            cancelable: true,
        })

        form.dispatchEvent(submitEvent)

        expect(handleSubmit).toHaveBeenCalledWith({
            1: [10, 20],
        })
    })

    it('передаёт текстовый ответ в onSubmit', () => {
        const survey = {
            TITLE: 'Опрос',
            QUESTIONS: [
                {
                    ID: 1,
                    TITLE: 'Расскажи о себе',
                    TYPE: 'text',
                    REQUIRED: false,
                    OPTIONS: [],
                },
            ],
        }

        const handleSubmit = vi.fn()

        render(
            <App
                survey={survey}
                onSubmit={handleSubmit}
            />
        )

        const input = screen.getByRole('textbox')

        input.value = 'Я изучаю React'

        const form = screen.getByRole('form')

        const submitEvent = new Event('submit', {
            bubbles: true,
            cancelable: true,
        })

        form.dispatchEvent(submitEvent)

        expect(handleSubmit).toHaveBeenCalledWith({
            1: 'Я изучаю React',
        })
    })

    it('отправляет ответы через API', async () => {
        const survey = {
            TITLE: 'Опрос',
            ID: 346,
            QUESTIONS: [
                {
                    ID: 1,
                    TITLE: 'Какой язык знаешь?',
                    TYPE: 'single',
                    REQUIRED: true,
                    OPTIONS: [
                        { ID: 10, TITLE: 'PHP' },
                        { ID: 20, TITLE: 'JavaScript' },
                    ],
                },
            ],
        }

        submitSurvey.mockResolvedValue({
            status: 'success',
            data: 126,
        })

        render(<App survey={survey} />)

        screen.getByLabelText('PHP').click()

        const form = screen.getByRole('form')

        const submitEvent = new Event('submit', {
            bubbles: true,
            cancelable: true,
        })

        form.dispatchEvent(submitEvent)

        expect(submitSurvey).toHaveBeenCalledWith(346, {
            1: 10,
        })
    })

    it('показывает состояние отправки', async () => {
        const survey = {
            TITLE: 'Опрос',
            ID: 346,
            QUESTIONS: [],
        }

        submitSurvey.mockReturnValue(new Promise(() => {}))

        render(<App survey={survey} />)

        const form = screen.getByRole('form')

        form.dispatchEvent(
            new Event('submit', {
                bubbles: true,
                cancelable: true,
            })
        )

        const button = await screen.findByRole('button', {
            name: 'Отправка...',
        })

        expect(button).toBeInTheDocument()
        expect(button).toBeDisabled()
    })

    it('показывает сообщение после успешной отправки', async () => {
        const survey = {
            TITLE: 'Опрос',
            ID: 346,
            QUESTIONS: [
                {
                    ID: 1,
                    TITLE: 'Какой язык знаешь?',
                    TYPE: 'single',
                    REQUIRED: true,
                    OPTIONS: [
                        { ID: 10, TITLE: 'PHP' },
                    ],
                },
            ],
        }

        submitSurvey.mockResolvedValue({
            status: 'success',
            data: 126,
        })

        render(<App survey={survey} />)

        screen.getByLabelText('PHP').click()

        screen.getByRole('form').dispatchEvent(
            new Event('submit', {
                bubbles: true,
                cancelable: true,
            })
        )

        expect(
            await screen.findByText('Опрос успешно отправлен')
        ).toBeInTheDocument()
    })

    it('показывает сообщение при ошибке отправки', async () => {
        const survey = {
            TITLE: 'Опрос',
            ID: 346,
            QUESTIONS: [
                {
                    ID: 1,
                    TITLE: 'Какой язык знаешь?',
                    TYPE: 'single',
                    REQUIRED: true,
                    OPTIONS: [
                        { ID: 10, TITLE: 'PHP' },
                    ],
                },
            ],
        }

        submitSurvey.mockResolvedValue({
            status: 'error',
            data: null,
        })

        render(<App survey={survey} />)

        screen.getByLabelText('PHP').click()

        screen.getByRole('form').dispatchEvent(
            new Event('submit', {
                bubbles: true,
                cancelable: true,
            })
        )

        expect(
            await screen.findByText('Не удалось отправить опрос')
        ).toBeInTheDocument()
    })

    it('показывает сообщение при ошибке сети', async () => {
        const survey = {
            TITLE: 'Опрос',
            ID: 346,
            QUESTIONS: [
                {
                    ID: 1,
                    TITLE: 'Какой язык знаешь?',
                    TYPE: 'single',
                    REQUIRED: true,
                    OPTIONS: [
                        { ID: 10, TITLE: 'PHP' },
                    ],
                },
            ],
        }

        submitSurvey.mockRejectedValue(new Error('Network error'))

        render(<App survey={survey} />)

        screen.getByLabelText('PHP').click()

        screen.getByRole('form').dispatchEvent(
            new Event('submit', {
                bubbles: true,
                cancelable: true,
            })
        )

        expect(
            await screen.findByText('Не удалось отправить опрос')
        ).toBeInTheDocument()
    })

    it('получает опрос через API при загрузке', async () => {
        const survey = {
            ID: 346,
            TITLE: 'Опрос через API',
            QUESTIONS: [],
        }

        getSurvey.mockResolvedValue({
            status: 'success',
            data: survey,
        })
        window.history.pushState({}, '', '/346')

        render(
            <App
                survey={{
                    TITLE: '',
                    QUESTIONS: [],
                }}
            />
        )

        expect(getSurvey).toHaveBeenCalledWith(346)

        expect(
            await screen.findByText('Опрос через API')
        ).toBeInTheDocument()
    })

    it('не падает, пока опрос загружается', () => {
        getSurvey.mockReturnValue(new Promise(() => {}))

        render(
            <App
                survey={undefined}
            />
        )

        expect(screen.getByText('Загрузка...')).toBeInTheDocument()
    })

})