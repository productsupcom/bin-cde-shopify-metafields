env.name                       = "bin-cde-app-skeleton"
env.description                = "bin-cde-app-skeleton"
env.maintainer                 = "Channel Dev Team <featureteam@productsup.com>"
env.homepage                   = "https://github.com/productsupcom/bin-cde-app-skeleton"
def slack_channel              = "#team-cd-dromund-kaas-notifications"
String runtimeContainer        = "php-cli"
env.box_version                = "3.11.1"
env.branch
env.version
env.gitCommitHash
env.gitCommitAuthor
env.gitCommitMessage
env.package_file_name
env.current_stage               = 'undefined'

def setCurrentStage(name) {
    env.current_stage = name
}

pipeline {
    agent { label 'jenkins-4'}

    options {
        buildDiscarder(
            logRotator(
                numToKeepStr: '5',
                artifactNumToKeepStr: '5'
            )
        )
        timestamps()
        timeout(time: 1, unit: 'HOURS')
        disableConcurrentBuilds()
        skipDefaultCheckout()
    }

    environment {
        COMPOSE_PROJECT_NAME = "${env.JOB_NAME}_${env.BUILD_ID}"
    }

    stages {
        // Checkout code with tags. The regular scm call does a flat checkout
        // and we need the tags to set the version
        stage("Checkout") {
            steps {
                setCurrentStage("Checkout")
                gitCheckout()
            }
        }

        // set version with the following scheme
        //   tags:   version = <tag>
        //   PR:     version = <latest tag>-<PR number>
        //   branch: version = <latest tag>-<branch name>
        stage('Prepare Info') {
            steps {
                setCurrentStage("Prepare Info")
                prepareInfo()
            }
        }

        // Pull docker image to use for the tests / build
        stage('Pull image and build code') {
            steps {
                setCurrentStage("Pull image and build code")
                pullAndBuild(runtimeContainer: "${runtimeContainer}")
            }
        }

        stage('Check APP_ENV') {
             steps {
                 script {
                     def envFile = readProperties file: '.env'
                     if (envFile['APP_ENV'] != 'prod') {
                        error("APP_ENV is not set to 'prod' in .env file")
                    }
                }
             }
        }

        stage('Running CS diff') {
            steps {
                setCurrentStage("Running CS diff")
                sh "docker-compose run ${runtimeContainer} composer csdiff"
            }
        }

        stage('Running Static Code Analysis') {
            steps {
                setCurrentStage("Running Static Code Analysis")
                sh "docker-compose run ${runtimeContainer} composer stan"
            }
        }

        stage('Running Unit Tests') {
            steps {
                setCurrentStage("Running Unit Tests")
                runUnitTests(runtimeContainer: "${runtimeContainer}")
            }
            post {
                failure {
                    junit skipPublishingChecks: true, testResults: 'reports/logfile.xml'
                }
            }
        }
    }

    // Run post jobs steps
    post {
        // failure sends a failure slack message if the pipeline exit with a failed state
        failure {
            slackSend message: "*${env.name} <${RUN_DISPLAY_URL}|failed>*\nat stage: *${env.current_stage}*\nfor *${env.branch}* at commit *${env.gitCommitHash}*\nwhen *${env.gitCommitAuthor}* performed\n> ${env.gitCommitMessage}", channel: "${slack_channel}", color: "danger"
        }
        // success sends a success slack message if the pipeline exit with a success state
        success {
            slackSend message: "*${env.name} <${RUN_DISPLAY_URL}|success>*.\nAll checks passed for *${env.branch}* \nat commit *${env.gitCommitHash}* \nwhen *${env.gitCommitAuthor}* performed \n> ${env.gitCommitMessage}.", channel: "${slack_channel}", color: "good"
        }
        // cleanup always run last and will trigger for both success and failure states
        cleanup {
            sh "docker-compose down --volumes"
            cleanWs deleteDirs: true
        }
    }
} 