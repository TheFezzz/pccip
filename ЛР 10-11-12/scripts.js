function calculateY(x) {
                const a = 0;
                const b = 7;
                const c = 5;
                const d = -3;
                const denominator = c * x + d; // 5x - 3
                
                try {
                    if (denominator === 0) {
                        throw new Error("Деление на ноль (знаменатель равен 0)");
                    }
                    
                    const y = b / denominator;
                    return y;
                } catch (error) {
                    alert("Ошибка при x=" + x + ": " + error.message);
                    return null;
                }
            }
            
            function calculateValues() {
                const xValues = [0.5, 0.6, 1.0, 1.5, 2.0];
                let calculationResults = `
                    <h3>Результаты расчетов для y = 7/(5x - 3):</h3>
                    <table>
                        <tr>
                            <th>x</th>
                            <th>y</th>
                            <th>Примечание</th>
                        </tr>
                `;
                
                xValues.forEach(x => {
                    const result = calculateY(x);
                    if (result !== null) {
                        calculationResults += `
                            <tr>
                                <td>${x}</td>
                                <td>${result.toFixed(6)}</td>
                                <td>${x === 0.6 ? '<span class="error">Проверка на x=0.6 (знаменатель = 0)</span>' : ''}</td>
                            </tr>
                        `;
                    } else {
                        calculationResults += `
                            <tr>
                                <td>${x}</td>
                                <td class="error">Ошибка</td>
                                <td class="error">Деление на ноль</td>
                            </tr>
                        `;
                    }
                });
                
                calculationResults += `</table>`;
                document.getElementById("task4Result").innerHTML = calculationResults;
            }