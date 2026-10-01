import { useEffect, useState } from 'react'
import { submitSurvey, getSurvey } from './api'

function App({ survey, onSubmit = () => {} }) {
    const [success, setSuccess] = useState(false)
    const [error, setError] = useState(false)
    const [loading, setLoading] = useState(false)
    const [loadedSurvey, setLoadedSurvey] = useState(survey)

    useEffect(() => {
        const surveyId = survey?.ID ?? Number(window.location.pathname.slice(1))

        getSurvey(surveyId).then((result) => {
            if (result.status === 'success') {
                setLoadedSurvey(result.data)
            }
        })
    }, [])

    if (!loadedSurvey) {
        return <p>Загрузка...</p>
    }

    return (
        <>
            {success && <p>Опрос успешно отправлен</p>}
            {error && <p>Не удалось отправить опрос</p>}

            <form
                className="survey-card"
                aria-label="Опрос"
                onSubmit={(event) => {
                    event.preventDefault()

                    const answers = {}

                    loadedSurvey.QUESTIONS.forEach((question) => {
                        if (question.TYPE === 'text') {
                            const input = event.currentTarget.querySelector(
                                `[name="question-${question.ID}"]`
                            )

                            answers[question.ID] = input.value
                        } else {
                            const selected = event.currentTarget.querySelectorAll(
                                `[name="question-${question.ID}"]:checked`
                            )

                            if (question.TYPE === 'multiple') {
                                answers[question.ID] = Array.from(selected).map(
                                    (input) => Number(input.value)
                                )
                            } else if (selected[0]) {
                                answers[question.ID] = Number(selected[0].value)
                            }
                        }
                    })

                    onSubmit(answers)

                    setLoading(true)

                    setLoading(true)

                    submitSurvey(loadedSurvey.ID, answers)
                        .then((result) => {
                            if (result.status === 'success') {
                                setSuccess(true)
                            } else {
                                setError(true)
                            }
                        })
                        .catch(() => {
                            setError(true)
                        })
                        .finally(() => {
                            setLoading(false)
                        })
                }}
            >
                <h1>{loadedSurvey.TITLE}</h1>

                {loadedSurvey.QUESTIONS.map((question) => (
                    <div className="question-card" key={question.ID}>
                        <h2>{question.TITLE}</h2>

                        {question.TYPE === 'text' && (
                            <input
                                type="text"
                                name={`question-${question.ID}`}
                                required={question.REQUIRED}
                            />
                        )}

                        {question.OPTIONS?.map((option) => (
                            <label className="answer-option" key={option.ID}>
                                <input
                                    type={
                                        question.TYPE === 'multiple'
                                            ? 'checkbox'
                                            : 'radio'
                                    }
                                    name={`question-${question.ID}`}
                                    value={option.ID}
                                />
                                <span>{option.TITLE}</span>
                            </label>
                        ))}
                    </div>
                ))}

                <button type="submit" disabled={loading}>
                    {loading ? 'Отправка...' : 'Отправить'}
                </button>
            </form>
        </>
    )
}

export default App